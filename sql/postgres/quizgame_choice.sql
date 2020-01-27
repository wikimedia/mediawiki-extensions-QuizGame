DROP SEQUENCE IF EXISTS quizgame_choice_choice_id_seq CASCADE;
CREATE SEQUENCE quizgame_choice_choice_id_seq;

CREATE TABLE IF NOT EXISTS quizgame_choice (
  choice_id INTEGER NOT NULL PRIMARY KEY DEFAULT nextval('quizgame_choice_choice_id_seq'),
  choice_q_id INTEGER NOT NULL default 0,
  choice_order INTEGER default 0,
  choice_text TEXT NOT NULL,
  choice_answer_count INTEGER NOT NULL default 0,
  choice_is_correct SMALLINT NOT NULL default 0
);

ALTER SEQUENCE quizgame_choice_choice_id_seq OWNED BY quizgame_choice.choice_id;

CREATE INDEX choice_q_id ON quizgame_choice (choice_q_id);
