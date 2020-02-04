DROP SEQUENCE IF EXISTS quizgame_user_view_uv_id_seq CASCADE;
CREATE SEQUENCE quizgame_user_view_uv_id_seq;

CREATE TABLE IF NOT EXISTS quizgame_user_view (
  uv_id INTEGER NOT NULL PRIMARY KEY DEFAULT nextval('quizgame_user_view_uv_id_seq'),
  uv_q_id INTEGER NOT NULL default 0,
  uv_actor INTEGER NOT NULL,
  uv_date TIMESTAMPTZ NOT NULL default NULL
);

ALTER SEQUENCE quizgame_user_view_uv_id_seq OWNED BY quizgame_user_view.uv_id;

CREATE INDEX uv_actor ON quizgame_user_view (uv_actor);
CREATE INDEX uv_q_id ON quizgame_user_view (uv_q_id);
