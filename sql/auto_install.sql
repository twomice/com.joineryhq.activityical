DROP TABLE IF EXISTS `civicrm_activityicalcache`;
DROP TABLE IF EXISTS `civicrm_activityicalcontact`;

-- /*******************************************************
-- *
-- * civicrm_activityicalcache
-- *
-- * Cached activity iCalendar feed contents, per contact
-- *
-- *******************************************************/
CREATE TABLE `civicrm_activityicalcache` (


     `id` int unsigned NOT NULL AUTO_INCREMENT  COMMENT 'Unique ActivityicalCache ID',
     `contact_id` int unsigned    COMMENT 'FK to Contact',
     `cache` mediumtext    COMMENT 'Cached feed output',
     `cached` timestamp    COMMENT 'Timestamp'
,
    PRIMARY KEY ( `id` )


,          CONSTRAINT FK_civicrm_activityicalcache_contact_id FOREIGN KEY (`contact_id`) REFERENCES `civicrm_contact`(`id`) ON DELETE CASCADE
)  ENGINE=InnoDB DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci  ;

-- /*******************************************************
-- *
-- * civicrm_activityicalcontact
-- *
-- * Per-contact data for activity iCalendar feed
-- *
-- *******************************************************/
CREATE TABLE IF NOT EXISTS `civicrm_activityicalcontact` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT 'Unique ActivityicalContact ID',
  `contact_id` int(10) unsigned DEFAULT NULL COMMENT 'FK to Contact',
  `hash` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL COMMENT 'Private hash per feed',
  PRIMARY KEY (`id`),
  UNIQUE KEY `contact_id` (`contact_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
