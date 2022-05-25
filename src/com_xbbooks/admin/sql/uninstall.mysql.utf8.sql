# DROP TABLE IF EXISTS `#__xbbooks`, `#__xbbookperson`, `#__xbbookcharacter`, `#__xbbookreviews`;

# `#__xbpersons`,
# `#__xbcharacters`,

DELETE FROM `#__ucm_history` WHERE ucm_type_id in 
	(select type_id from `#__content_types` where type_alias in ('com_xbbooks.book','com_xbbooks.person','com_xbbooks.character','com_xbbooks.review','com_xbbooks.category'));
DELETE FROM `#__ucm_base` WHERE ucm_type_id in 
	(select type_id from `#__content_types` WHERE type_alias in ('com_xbbooks.book','com_xbbooks.person','com_xbbooks.character','com_xbbooks.review','com_xbbooks.category'));
DELETE FROM `#__ucm_content` WHERE core_type_alias in ('com_xbbooks.book','com_xbbooks.person','com_xbbooks.character','com_xbbooks.review','com_xbbooks.category');
DELETE FROM `#__contentitem_tag_map` WHERE type_alias in ('com_xbbooks.book','com_xbbooks.person','com_xbbooks.character','com_xbbooks.review','com_xbbooks.category');
DELETE FROM `#__content_types` WHERE type_alias in ('com_xbbooks.book','com_xbbooks.person','com_xbbooks.character','com_xbbooks.review','com_xbbooks.category');
