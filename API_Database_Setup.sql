#RUN THE FOLLOWING SQL COMMANDS TO SET UP API DATABASE FUNCTIONALITY…

SET NAMES utf8;
SET FOREIGN_KEY_CHECKS = 0;

CREATE TABLE `api_keys` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `public_key` varchar(100) CHARACTER SET latin1 NOT NULL,
  `private_key` varchar(500) CHARACTER SET latin1 NOT NULL,
  PRIMARY KEY (`id`),
  KEY `index_public_key` (`public_key`),
  KEY `index_private_key` (`private_key`)
) ENGINE=MyISAM AUTO_INCREMENT=1 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

INSERT INTO api_keys (public_key, private_key) VALUES(SHA1(UUID()), SHA2(UUID(), 512));
COMMIT;


CREATE TABLE `token_signatures` (
  `token` varchar(600) CHARACTER SET latin1 NOT NULL,
  `public_key` varchar(255) CHARACTER SET latin1 NOT NULL,
  `date_time` datetime NOT NULL,
  PRIMARY KEY (`token`),
  KEY `index_public_key_nonce` (`public_key`,`token`),
  KEY `index_date_time` (`date_time`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;


CREATE TABLE `api_method_schema` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `methodName` varchar(100) NOT NULL,
  `spName` varchar(100) NOT NULL,
  `spParams` varchar(1000) NOT NULL,
  `testData` varchar(1000) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=latin1;

BEGIN;
INSERT INTO `api_method_schema` VALUES ('1', 'generateToken', 'spGenerateToken', 'key', ''), ('2', 'validateToken', 'spValidateToken', 'token', ''), ('3', 'TestAPI', 'spTestAPI', '', '');
COMMIT;



delimiter ;;
CREATE PROCEDURE `spGenerateToken`(iPublicKey varchar(100))
LANGUAGE SQL
NOT DETERMINISTIC
CONTAINS SQL
SQL SECURITY DEFINER
COMMENT ''
BEGIN

DELETE FROM token_signatures WHERE ABS(TIMESTAMPDIFF(MINUTE, NOW(), date_time)) >= 2;

SET @token = SHA2((SELECT SHA1(CONCAT(public_key, private_key, UNIX_TIMESTAMP())) AS token FROM api_keys WHERE public_key = iPublicKey), 512);
DELETE FROM token_signatures WHERE token = @token;
INSERT INTO token_signatures VALUES(@token, iPublicKey, NOW());

SELECT @token AS token;

END
 ;;
delimiter ;


delimiter ;;
CREATE PROCEDURE `spValidateToken`(iToken varchar(1000))
LANGUAGE SQL
NOT DETERMINISTIC
CONTAINS SQL
SQL SECURITY DEFINER
COMMENT ''
BEGIN

DELETE FROM token_signatures WHERE ABS(TIMESTAMPDIFF(MINUTE, NOW(), date_time)) >= 2;

SELECT a.public_key, a.private_key, token
FROM token_signatures t
INNER JOIN api_keys a ON a.public_key = t.public_key
WHERE t.token = iToken;

END
 ;;
delimiter ;


-- ----------------------------
-- Procedure structure for `spTestAPI`
-- ----------------------------
DROP PROCEDURE IF EXISTS `spTestAPI`;
DELIMITER ;;
CREATE PROCEDURE `spTestAPI`()
BEGIN

SELECT 0 AS error, 'API Works' AS message;

END
;;
DELIMITER ;


SET FOREIGN_KEY_CHECKS = 1;

