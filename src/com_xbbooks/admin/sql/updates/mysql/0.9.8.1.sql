# v0.9.8 change cat_date to acq_date
ALTER TABLE `#__xbbooks` CHANGE `cat_date` `acq_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;
# v0.9.8 adding last_read column to xbbooks
ALTER TABLE `#__xbbooks` ADD `last_read` DATETIME NULL DEFAULT NULL AFTER `acq_date`;
# set last_read to latest review date
UPDATE `#__xbbooks`  AS a SET `last_read` =  (SELECT MAX(r.rev_date) FROM `#__xbbookreviews` AS r WHERE r.book_id=a.id  AND r.rev_date IS NOT NULL);
