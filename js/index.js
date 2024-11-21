const test_data = {
	'field_data': {
		'day': 1
	},
	'player_items': [
		{
			"item_id": "test_item",
			"name": "テストアイテム",
			"description": "これはテストアイテムです。",
			"hunger": 0,
			"dry": 0,
			"stamina": 0,
			"stress": 0,
			"amount": 3
		}
	],
	'table': [
		/**
			p: player
			m: monster
			g: goal
			e: event
		*/
		[ " ", " ", " ", "e", " " ],
		[ " ", "p", " ", " ", "m" ],
		[ " ", " ", " ", " ", " " ],
		[ " ", " ", " ", " ", " " ],
		[ "m", " ", " ", " ", "g" ]
	]
}

function display_field(data) {
	const table = document.getElementById('game_table');
	data.forEach((row, i) => {
		const tr = document.createElement('tr');
		row.forEach((cell, j) => {
			const td = document.createElement('td');
			td.textContent = cell;
			tr.appendChild(td);
		});
		table.appendChild(tr);
	});
}

function display_items(data) {

}

function test(data) {
	display_field(data);
}

test(test_data.table);