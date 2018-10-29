CREATE TABLE IF NOT EXISTS /*_*/quizgame_user_view (
  uv_id int(11) unsigned NOT NULL PRIMARY KEY auto_increment,
  uv_q_id int(11) unsigned NOT NULL default 0,
  uv_user_id int(11) unsigned NOT NULL default 0,
  uv_user_name varchar(255) NOT NULL default '',
  uv_date datetime default NULL
) /*$wgDBTableOptions*/;

CREATE INDEX /*i*/uv_user_id ON /*_*/quizgame_user_view (uv_user_id);
CREATE INDEX /*i*/uv_q_id ON /*_*/quizgame_user_view (uv_q_id);
