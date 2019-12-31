CREATE TABLE IF NOT EXISTS /*_*/quizgame_choice (
  choice_id int(11) NOT NULL PRIMARY KEY auto_increment,
  choice_q_id int(11) NOT NULL default 0,
  choice_order int(5) default 0,
  choice_text text NOT NULL,
  choice_answer_count int(11) NOT NULL default 0,
  choice_is_correct tinyint(4) NOT NULL default 0
) /*$wgDBTableOptions*/;

CREATE INDEX /*i*/choice_q_id ON /*_*/quizgame_choice (choice_q_id);
