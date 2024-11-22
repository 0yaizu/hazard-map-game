<?php
// データベース接続情報
$host = "localhost";
$dbname = "kose0907";
$user = "kose0907";
$password = "kqdWkizR";

// プレイヤーデータ取得処理
$playerData = null;
$items = [];
try {
    $dsn = "pgsql:host=$host;dbname=$dbname";
    $pdo = new PDO($dsn, $user, $password);

    // プレイヤーデータを取得（最新の1件）
    $query = 'SELECT * FROM players WHERE id = (SELECT MAX(id) FROM players)';
    $stmt = $pdo->query($query);
    $playerData = $stmt->fetch(PDO::FETCH_ASSOC);

    // アイテムデータを取得
    $query = 'SELECT * FROM items';
    $stmt = $pdo->query($query);
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    echo "<p style='color: red;'>エラー: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// パワーアイテムデータを取得
$powerItems = [];
try {
    $query = 'SELECT * FROM power_items';
    $stmt = $pdo->query($query);
    $powerItems = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "<p style='color: red;'>エラー: " . htmlspecialchars($e->getMessage()) . "</p>";
}

// パワーアイテムデータをJSON形式に変換
$powerItemsJson = json_encode($powerItems);
// アイテムデータをJSON形式に変換
$itemsJson = json_encode($items);
?>

<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>完全版ターン制ゲーム</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet/dist/leaflet.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/leaflet/dist/leaflet.js"></script>
    <style>
        #map {
            width: 100%;
            height: 80vh;
        }
        .grid-cell {
            fill-opacity: 0.2;
            stroke: #000;
            stroke-width: 1;
        }
        .highlight {
            fill: red !important;
            fill-opacity: 0.5 !important;
        }
        .item-marker span {
            font-size: 24px;
        }
        .enemy-marker {
            font-size: 24px;
        }
        .player-marker {
            color: blue;
            font-size: 26px;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container-fluid my-4">
        <div class="row">
            <div class="col-md-3">
                <h3>プレイヤーステータス</h3>
                <ul class="list-group">
                    <li class="list-group-item">
                        <strong>体力:</strong> <span id="health"><?= $playerData['health'] ?? '-' ?></span>
                    </li>
                    <li class="list-group-item">
                        <strong>空腹度:</strong> <span id="hunger"><?= $playerData['hunger'] ?? '-' ?></span>
                    </li>
                    <li class="list-group-item">
                        <strong>喉の渇き:</strong> <span id="thirst"><?= $playerData['thirst'] ?? '-' ?></span>
                    </li>
                    <li class="list-group-item">
                        <strong>ターン数:</strong> <span id="turn">1</span>
                    </li>
                </ul>
                <div class="mt-4">
                    <h4 id="status">昼です。プレイヤーの番です。</h4>
                </div>
            </div>
            <div class="col-md-9">
                <div id="map"></div>
            </div>
        </div>
    </div>

    <script>
        // アイテムデータをPHPから取得
        const items = <?= json_encode($items) ?>;
        // PHPからパワーアイテムデータを取得
        const powerItems = <?= $powerItemsJson ?>;


        // アイテムアイコンを取得
        function getItemIcon(jaName) {
            switch (jaName) {
                case "リンゴ": return "🍎";
                case "水のボトル": return "💧";
                case "パン": return "🍞";
                case "ジュース": return "🍹";
                case "肉": return "🍖";
                default: return "❓";
            }
        }

        // 江東区中心
        const kotoCenter = [35.672977, 139.817401];

        // マップの初期化
        const map = L.map('map').setView(kotoCenter, 15);

        // タイルレイヤー追加
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 18,
        }).addTo(map);

        // 江東区の範囲
        const kotoBounds = L.latLngBounds(
            [35.645067, 139.785546], // 南西
            [35.699437, 139.849493]  // 北東
        );

        // マス目の設定
        const rows = 30, cols = 30;
        const cellSizeLat = (kotoBounds.getNorth() - kotoBounds.getSouth()) / rows;
        const cellSizeLng = (kotoBounds.getEast() - kotoBounds.getWest()) / cols;

        // グリッド生成
        const cells = [];
        const cellLayers = [];
        for (let i = 0; i < rows; i++) {
            cells[i] = [];
            cellLayers[i] = [];
            for (let j = 0; j < cols; j++) {
                const lat1 = kotoBounds.getSouth() + cellSizeLat * i;
                const lng1 = kotoBounds.getWest() + cellSizeLng * j;
                const lat2 = lat1 + cellSizeLat;
                const lng2 = lng1 + cellSizeLng;

                // 四角形を描画
                const rect = L.rectangle([[lat1, lng1], [lat2, lng2]], {
                    color: "#3388ff",
                    weight: 1,
                    fillOpacity: 0.2,
                    className: "grid-cell"
                }).addTo(map);

                cells[i][j] = { lat: (lat1 + lat2) / 2, lng: (lng1 + lng2) / 2 };
                cellLayers[i][j] = rect;
            }
        }

        // プレイヤーと敵のデータ
        let playerPosition = { row: 15, col: 15 };
        let hunger = <?= $playerData['hunger'] ?? 100 ?>;
        let thirst = <?= $playerData['thirst'] ?? 100 ?>;
        let turn = 1;
        let isPlayerTurn = true;

        // 敵のデータ
        const enemies = [
            { row: Math.floor(Math.random() * rows), col: Math.floor(Math.random() * cols) },
            { row: Math.floor(Math.random() * rows), col: Math.floor(Math.random() * cols) }
        ];
        const enemyMarkers = [];

        // アイテム配置
        const placedItems = [];
        const itemMarkers = [];
        items.forEach(item => {
            // 各アイテムを3個配置
            for (let count = 0; count < 3; count++) {
                const row = Math.floor(Math.random() * rows);
                const col = Math.floor(Math.random() * cols);

                placedItems.push({ ...item, row, col });

                const marker = L.divIcon({
                    className: 'item-marker',
                    html: `<span title="${item.description}">${getItemIcon(item.ja_name)}</span>`,
                });

                itemMarkers.push(L.marker(cells[row][col], { icon: marker }).addTo(map));
            }
        });

        // パワーアイテム配置データ
        const placedPowerItems = [];

        // パワーアイテムをマップ上にランダム配置
        powerItems.forEach(item => {
            const row = Math.floor(Math.random() * rows);
            const col = Math.floor(Math.random() * cols);

            // 配置データを保存
            placedPowerItems.push({ ...item, row, col });

            // アイテムマーカーを追加
            const marker = L.divIcon({
                className: 'item-marker',
                html: `<span title="${item.description}">💥</span>` // アイコン変更可
            });

            L.marker(cells[row][col], { icon: marker }).addTo(map);
        });

        // プレイヤーマーカー
        const playerMarker = L.marker(cells[playerPosition.row][playerPosition.col], {
            title: "プレイヤー",
            icon: L.divIcon({ className: "player-marker", html: "🧍" })
        }).addTo(map);

        // 隣接マスを強調
        function highlightAdjacentCells() {
            clearHighlights();
            const directions = [
                { row: -1, col: 0 },
                { row: 1, col: 0 },
                { row: 0, col: -1 },
                { row: 0, col: 1 },
            ];
            directions.forEach(({ row, col }) => {
                const adjRow = playerPosition.row + row;
                const adjCol = playerPosition.col + col;
                if (adjRow >= 0 && adjRow < rows && adjCol >= 0 && adjCol < cols) {
                    cellLayers[adjRow][adjCol].setStyle({ color: "red", fillOpacity: 0.5 });
                }
            });
        }

        // 強調を解除
        function clearHighlights() {
            for (let i = 0; i < rows; i++) {
                for (let j = 0; j < cols; j++) {
                    cellLayers[i][j].setStyle({ color: "#3388ff", fillOpacity: 0.2 });
                }
            }
        }

        // マスクリックでプレイヤー移動 (既存コードの修正)
        map.on("click", (e) => {
            if (!isPlayerTurn) return;

            const { lat, lng } = e.latlng;
            const newRow = Math.floor((lat - kotoBounds.getSouth()) / cellSizeLat);
            const newCol = Math.floor((lng - kotoBounds.getWest()) / cellSizeLng);

            const isAdjacent = Math.abs(newRow - playerPosition.row) + Math.abs(newCol - playerPosition.col) === 1;

            if (isAdjacent && newRow >= 0 && newRow < rows && newCol >= 0 && newCol < cols) {
                playerPosition = { row: newRow, col: newCol };
                playerMarker.setLatLng(cells[playerPosition.row][playerPosition.col]);

                hunger = Math.max(0, hunger - 5);
                thirst = Math.max(0, thirst - 5);

                // アイテム取得処理
                const item = placedItems.find(i => i.row === newRow && i.col === newCol);
                if (item) {
                    applyItemEffect(item); // アイテムの効果を適用
                    placedItems.splice(placedItems.indexOf(item), 1);

                    // アイテムマーカー削除
                    const marker = itemMarkers.find(m => m.getLatLng().lat === cells[newRow][newCol].lat && m.getLatLng().lng === cells[newRow][newCol].lng);
                    if (marker) map.removeLayer(marker);
                }

                updateStatus();
                highlightAdjacentCells();
                startEnemyTurn();
            }
        });

        // アイテム取得時の効果適用
        function applyItemEffect(item) {
            // 空腹度の回復
            if (item.hunger) {
                hunger = Math.min(100, hunger + item.hunger);
            }

            // 喉の渇きの回復
            if (item.thirst) {
                thirst = Math.min(100, thirst + item.thirst);
            }

            // 体力の回復
            if (item.health) {
                const healthElement = document.getElementById("health");
                const currentHealth = parseInt(healthElement.textContent, 10) || 0;
                const newHealth = Math.min(100, currentHealth + item.health);
                healthElement.textContent = newHealth;
            }

            // 画面上のステータスを更新
            updateStatus();

            // アイテム取得メッセージを表示
            alert(`${item.name} を使用しました！効果: ${item.description}`);
        }

        function updateStatus() {
            document.getElementById("hunger").ftextContent = hunger;
            document.getElementById("thirst").textContent = thirst;
            // 体力は別途更新
        }

        // パワーアイテム効果を適用
        function applyPowerItemEffect(item) {
            switch (item.effect) {
                case "enemy_kill":
                    alert("銃を使って敵を倒しました！");
                    break;
                case "move_2_spaces":
                    alert("スニークブーツで2マス移動できます！");
                    break;
                case "freeze_enemy":
                    alert("凍結トラップで敵を一時的に停止させました！");
                    break;
                default:
                    alert("未知の効果が発動しました！");
            }
        }

        // 敵のターン処理
        function startEnemyTurn() {
            isPlayerTurn = false;
            document.getElementById("status").textContent = "夜です。敵の番です。";

            setTimeout(() => {
                moveEnemies();
                checkGameOver();
                endEnemyTurn();
            }, 1000);
        }

        function endEnemyTurn() {
            isPlayerTurn = true;
            turn++;
            document.getElementById("status").textContent = `昼です。プレイヤーの番です。(ターン: ${turn})`;

            if (turn % 3 === 0) {
                enemies.push({
                    row: Math.floor(Math.random() * rows),
                    col: Math.floor(Math.random() * cols)
                });
            }

            highlightAdjacentCells();
        }

        function moveEnemies() {
            enemies.forEach(enemy => {
                const rowDiff = playerPosition.row - enemy.row;
                const colDiff = playerPosition.col - enemy.col;

                if (Math.abs(rowDiff) > Math.abs(colDiff)) {
                    enemy.row += Math.sign(rowDiff); // 縦方向の優先移動
                } else {
                    enemy.col += Math.sign(colDiff); // 横方向の移動
                }
            });

            updateEnemyMarkers();
        }

        // 敵マーカー更新
        function updateEnemyMarkers() {
            enemyMarkers.forEach(marker => map.removeLayer(marker));
            enemyMarkers.length = 0;

            enemies.forEach(enemy => {
                const marker = L.marker(cells[enemy.row][enemy.col], {
                    title: "敵",
                    icon: L.divIcon({ html: "👾", className: "enemy-marker" }),
                });
                enemyMarkers.push(marker.addTo(map));
            });
        }

        // ゲームオーバー判定
        function checkGameOver() {
            enemies.forEach(enemy => {
                if (enemy.row === playerPosition.row && enemy.col === playerPosition.col) {
                    alert("ゲームオーバー！敵に捕まりました！");
                    location.reload();
                }
            });
        }

        // ステータス更新
        function updateStatus() {
            document.getElementById("hunger").textContent = hunger;
            document.getElementById("thirst").textContent = thirst;
            document.getElementById("turn").textContent = turn;
        }

        // 初期化
        updateEnemyMarkers();
        highlightAdjacentCells();
    </script>
</body>
</html>