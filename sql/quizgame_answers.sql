CREATE TABLE IF NOT EXISTS /*_*/quizgame_answers (
  a_id int(10) unsigned NOT NULL PRIMARY KEY auto_increment,
  a_q_id int(10) unsigned NOT NULL default 0,
  a_choice_id int(11) unsigned NOT NULL default 0,
  a_actor bigint unsigned NOT NULL,
  a_points int(11) unsigned NOT NULL default 0,
  a_date datetime default NULL
) /*$wgDBTableOptions*/;

CREATE INDEX /*i*/a_q_id ON /*_*/quizgame_answers (a_q_id);
CREATE INDEX /*i*/a_choice_id ON /*_*/quizgame_answers (a_choice_id);
CREATE INDEX /*i*/a_actor ON /*_*/quizgame_answers (a_actor);
