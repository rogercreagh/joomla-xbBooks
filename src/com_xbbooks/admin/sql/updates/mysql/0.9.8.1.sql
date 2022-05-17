# v0.9.8 change cat_date to acq_date
ALTER TABLE `#__xbbooks` CHANGE `cat_date` `acq_date` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP;
# v0.9.8 adding read_date column to xbbooks
ALTER TABLE `#__xbbooks` ADD `read_date` DATETIME NULL DEFAULT NULL AFTER `acq_date`;
# set read_date to latest review date
UPDATE `#__xbbooks`  AS a SET `read_date` =  (SELECT MAX(r.rev_date) FROM `#__xbbookreviews` AS r WHERE r.book_id=a.id  AND r.rev_date IS NOT NULL);
