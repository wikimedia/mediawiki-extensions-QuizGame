CREATE TABLE IF NOT EXISTS /*_*/quizgame_questions (
  q_id int(11) unsigned NOT NULL PRIMARY KEY auto_increment,
  q_actor bigint unsigned NOT NULL,
  -- One of the QuizGameHome::$FLAG_* static variables
  -- 0 = QuizGameHome::$FLAG_NONE = default state
  -- 1 = QuizGameHome::$FLAG_FLAGGED = flagged
  -- 2 = QuizGameHome::$FLAG_PROTECT = protected
  --`q_flag` enum('NONE','PROTECT','FLAGGED') NOT NULL default 'NONE',
  q_flag tinyint(2) NOT NULL default 0,
  q_text varchar(255) NOT NULL default '',
  q_answer_count int(11) default 0,
  q_answer_correct_count int(11) default 0,
  -- This was originally varchar(45), which sucked
  q_picture varchar(255) NOT NULL default '',
  q_date datetime default NULL,
  q_random double unsigned default 0,
  q_comment varchar(255) default ''
) /*$wgDBTableOptions*/;

CREATE INDEX /*i*/q_actor ON /*_*/quizgame_questions (q_actor);
CREATE INDEX /*i*/q_random ON /*_*/quizgame_questions (q_random);
