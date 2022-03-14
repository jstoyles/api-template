#RUN THE FOLLOWING SQL COMMANDS TO SET UP API DATABASE FUNCTIONALITY…

SET NAMES utf8;
SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE `api_keys` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `public_key` varchar(100) CHARACTER SET latin1 NOT NULL,
  `private_key` varchar(500) CHARACTER SET latin1 NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_public_key` (`public_key`),
  KEY `idx_private_key` (`private_key`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

INSERT INTO api_keys (public_key, private_key) VALUES(SHA1(UUID()), SHA2(UUID(), 512));
COMMIT;


CREATE TABLE `token_signatures` (
  `token` varchar(600) CHARACTER SET latin1 NOT NULL,
  `public_key` varchar(255) CHARACTER SET latin1 NOT NULL,
  `date_time` datetime NOT NULL,
  PRIMARY KEY (`token`),
  KEY `idx_public_key_nonce` (`public_key`,`token`),
  KEY `idx_date_time` (`date_time`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;


delimiter ;;
CREATE PROCEDURE `GenerateToken`(i_public_key varchar(100))
LANGUAGE SQL
NOT DETERMINISTIC
CONTAINS SQL
SQL SECURITY DEFINER
COMMENT 'Used to generate a token using a valid API key ~ {"result":true,"token":"bdecb71d1752084e23ba1cfb...","msg":"success"}'
BEGIN

DELETE FROM token_signatures WHERE ABS(TIMESTAMPDIFF(MINUTE, NOW(), date_time)) >= 2;

SET @token = SHA2((SELECT SHA1(CONCAT(public_key, private_key, UNIX_TIMESTAMP())) AS token FROM api_keys WHERE public_key = i_public_key), 512);
DELETE FROM token_signatures WHERE token = @token;
INSERT INTO token_signatures VALUES(@token, i_public_key, NOW());

SELECT @token AS token;

END
 ;;
delimiter ;


delimiter ;;
CREATE PROCEDURE `ValidateToken`(i_token varchar(255), i_public_key varchar(255), i_authcode varchar(255))
LANGUAGE SQL
NOT DETERMINISTIC
CONTAINS SQL
SQL SECURITY DEFINER
COMMENT 'Used to validate a given token'
BEGIN

DELETE FROM token_signatures WHERE ABS(TIMESTAMPDIFF(MINUTE, NOW(), date_time)) >= 2;

SET @private_key = (SELECT private_key FROM api_keys WHERE public_key = i_public_key);

SET @authcode = SHA2(CONCAT(i_public_key, @private_key, i_token), 512);

IF(@authcode = i_authcode)THEN
  SELECT a.public_key, token, true AS response, 'Token Valid' AS message
  FROM token_signatures t
  INNER JOIN api_keys a ON a.public_key = t.public_key
  WHERE t.token = i_token;
ELSE
  SELECT '' AS public_key, '' AS token, false AS response, 'Invalid Auth Code' AS message;
END IF;

END
 ;;
delimiter ;


-- ----------------------------
-- Procedure structure for `TestAPI`
-- ----------------------------
DROP PROCEDURE IF EXISTS `TestAPI`;
DELIMITER ;;
CREATE PROCEDURE `TestAPI`()
COMMENT 'Can be used to test the current status of the API ~ {"result":true,"msg":"success","data":[{"error":"0","message":"API Works"}]}'
BEGIN

SELECT 0 AS error, 'API Works' AS message;

END
;;
DELIMITER ;


-- ----------------------------
-- Procedure structure for `_GET_STORED_PROCEDURES`
-- ----------------------------
DROP PROCEDURE IF EXISTS `_GET_STORED_PROCEDURES`;
DELIMITER ;;
CREATE PROCEDURE `_GET_STORED_PROCEDURES`()
COMMENT 'Used to generate a list of available stored proceudres that can be used for API endpoints'
BEGIN

SELECT r.ROUTINE_NAME AS method, r.ROUTINE_COMMENT AS comments
, CASE WHEN p.SPECIFIC_NAME IS NULL THEN '' ELSE GROUP_CONCAT(p.PARAMETER_NAME ORDER BY p.ORDINAL_POSITION) END AS parameters
, CASE WHEN p.SPECIFIC_NAME IS NULL THEN '' ELSE GROUP_CONCAT(REPLACE(REPLACE(REPLACE(REPLACE(p.DATA_TYPE, 'varchar', 'String'), 'text', 'String'), 'int','Integer'), 'date','Date') ORDER BY p.ORDINAL_POSITION) END AS parameter_data_types
FROM information_schema.ROUTINES r
LEFT JOIN information_schema.PARAMETERS p ON p.SPECIFIC_NAME = r.SPECIFIC_NAME AND p.PARAMETER_MODE = 'IN'
WHERE r.SPECIFIC_NAME <> '_GET_STORED_PROCEDURES'
GROUP BY r.SPECIFIC_NAME;

END
;;
DELIMITER ;

SET FOREIGN_KEY_CHECKS = 1;
