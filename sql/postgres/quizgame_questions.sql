DROP SEQUENCE IF EXISTS quizgame_questions_q_id_seq CASCADE;
CREATE SEQUENCE quizgame_questions_q_id_seq;

CREATE TABLE IF NOT EXISTS quizgame_questions (
  q_id INTEGER NOT NULL PRIMARY KEY DEFAULT nextval('quizgame_questions_q_id_seq'),
  q_actor INTEGER NOT NULL,
  q_flag SMALLINT NOT NULL default 0,
  q_text TEXT NOT NULL default '',
  q_answer_count INTEGER default 0,
  q_answer_correct_count INTEGER default 0,
  q_picture TEXT NOT NULL default '',
  q_date TIMESTAMPTZ NOT NULL default NULL,
  q_random DOUBLE PRECISION default 0,
  q_comment TEXT default ''
);

ALTER SEQUENCE quizgame_questions_q_id_seq OWNED BY quizgame_questions.q_id;

CREATE INDEX q_actor ON quizgame_questions (q_actor);
CREATE INDEX q_random ON quizgame_questions (q_random);
