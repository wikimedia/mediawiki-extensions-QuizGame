DROP SEQUENCE IF EXISTS quizgame_answers_a_id_seq CASCADE;
CREATE SEQUENCE quizgame_answers_a_id_seq;

CREATE TABLE IF NOT EXISTS quizgame_answers (
  a_id INTEGER NOT NULL PRIMARY KEY DEFAULT nextval('quizgame_answers_a_id_seq'),
  a_q_id INTEGER NOT NULL default 0,
  a_choice_id INTEGER NOT NULL default 0,
  a_actor INTEGER NOT NULL,
  a_points INTEGER NOT NULL default 0,
  a_date TIMESTAMPTZ NOT NULL default NULL
);

ALTER SEQUENCE quizgame_answers_a_id_seq OWNED BY quizgame_answers.a_id;

CREATE INDEX a_q_id ON quizgame_answers (a_q_id);
CREATE INDEX a_choice_id ON quizgame_answers (a_choice_id);
CREATE INDEX a_actor ON quizgame_answers (a_actor);
