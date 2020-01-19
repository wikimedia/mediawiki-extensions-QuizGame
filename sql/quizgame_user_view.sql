CREATE TABLE IF NOT EXISTS /*_*/quizgame_user_view (
  uv_id int(11) unsigned NOT NULL PRIMARY KEY auto_increment,
  uv_q_id int(11) unsigned NOT NULL default 0,
  uv_actor bigint unsigned NOT NULL,
  uv_date datetime default NULL
) /*$wgDBTableOptions*/;

CREATE INDEX /*i*/uv_actor ON /*_*/quizgame_user_view (uv_actor);
CREATE INDEX /*i*/uv_q_id ON /*_*/quizgame_user_view (uv_q_id);
