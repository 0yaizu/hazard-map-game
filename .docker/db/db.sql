-- user table
create table if not exists users (id bigserial, user_id VARCHAR(20), name VARCHAR(20), email TEXT, password TEXT)
