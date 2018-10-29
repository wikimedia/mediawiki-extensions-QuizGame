CREATE TABLE IF NOT EXISTS /*_*/quizgame_answers (
  a_id int(10) unsigned NOT NULL PRIMARY KEY auto_increment,
  a_q_id int(10) unsigned NOT NULL default 0,
  a_choice_id int(11) unsigned NOT NULL default 0,
  a_user_id int(11) unsigned NOT NULL default 0,
  a_user_name varchar(255) NOT NULL default '',
  a_points int(11) unsigned NOT NULL default 0,
  a_date datetime default NULL
) /*$wgDBTableOptions*/;

CREATE INDEX /*i*/a_q_id ON /*_*/quizgame_answers (a_q_id);
CREATE INDEX /*i*/a_choice_id ON /*_*/quizgame_answers (a_choice_id);
CREATE INDEX /*i*/a_user_id ON /*_*/quizgame_answers (a_user_id);
CREATE INDEX /*i*/a_user_name ON /*_*/quizgame_answers (a_user_name);
