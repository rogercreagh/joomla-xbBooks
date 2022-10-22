# v0.9.9.8 insert first_read
ALTER TABLE `#__xbbooks` ADD `first_read` DATE NULL DEFAULT NULL AFTER `acq_date`;
#change last_read and acq_date and rev_date from datetime to date
ALTER TABLE `#__xbbooks` CHANGE `last_read` `last_read` DATE NULL DEFAULT NULL;
ALTER TABLE `#__xbbooks` CHANGE `acq_date` `acq_date` DATE NULL DEFAULT NULL;
ALTER TABLE `#__xbbookreviews` CHANGE `rev_date` `rev_date` DATE NULL DEFAULT NULL;
#set first_seen to first review date if there is one
UPDATE `#__xbbooks` AS a SET `first_read` =  (SELECT MIN(r.rev_date) FROM `#__xbbookreviews` AS r WHERE r.book_id=a.id  AND r.rev_date IS NOT NULL);
#if first_seen still null and no review but there is a last_read date then set first_read to last_read
UPDATE `#__xbbooks` SET `first_read` = `last_read` WHERE ISNULL(`first_read`);
#if last_seen is null set it to first_read (both should always be set if one is)
UPDATE `#__xbbooks` SET `last_read` = `first_read` WHERE ISNULL(`last_read`);
ALTER TABLE `#__xbbooks` DROP `acq_date`;
