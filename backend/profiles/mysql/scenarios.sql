/*
Navicat MySQL Data Transfer

Source Server         : mysql
Source Server Version : 50617
Source Host           : localhost:3306
Source Database       : demo

Target Server Type    : MYSQL
Target Server Version : 50617
File Encoding         : 65001

Date: 2017-05-28 17:46:13
*/

SET FOREIGN_KEY_CHECKS=0;

-- ----------------------------
-- Table structure for scenario1
-- ----------------------------
DROP TABLE IF EXISTS `scenario1`;
CREATE TABLE "scenario1" (
  "id" bigint(11) unsigned NOT NULL AUTO_INCREMENT,
  "name" varchar(100) NOT NULL,
  "int" int(11) DEFAULT NULL,
  "currency" decimal(18,2) DEFAULT NULL,
  "float" float(255,5) DEFAULT NULL,
  "bool" tinyint(1) DEFAULT NULL,
  "date" date DEFAULT NULL,
  "datetime" datetime DEFAULT NULL,
  "text" text,
  "email" varchar(100) DEFAULT NULL,
  "varchar_10" varchar(10) NOT NULL,
  "id_detail" int(2) DEFAULT NULL,
  "enum" enum('ok','not_ok') DEFAULT NULL,
  "default" varchar(255) NOT NULL DEFAULT 'FOOBAR',
  PRIMARY KEY ("id")
);

-- ----------------------------
-- Records of scenario1
-- ----------------------------
INSERT INTO `scenario1` VALUES ('1', 'Andon', '88', '1.32', '1.00000', '1', '2017-05-05', '2017-05-04 00:00:00', 'asdfasdfasdf\r\n\r\n\r\n\r\nadfl;djasfa', 'Nam@quisdiamluctus.org', '1234567890', '1', 'ok', 'FOOBAR');
INSERT INTO `scenario1` VALUES ('2', 'Atkins', '44', '5.55', '1.50000', '0', '0000-00-00', '2017-05-05 12:30:00', null, 'arcu.et.pede@musProin.cam', 'Ishmael', '2', 'not_ok', 'foobar');
INSERT INTO `scenario1` VALUES ('3', 'Hamilton', '10', '10.50', '1.00000', '1', '0000-00-00', null, null, 'dui@duiCras.edu', 'Mohammad', '3', 'ok', 'FOOBAR');
INSERT INTO `scenario1` VALUES ('4', 'Nicholas', '18', '1.92', '2.00000', '0', '0000-00-00', null, null, 'mollis@eutellus.co.uk', 'Troy', '4', null, 'FOOBAR');
INSERT INTO `scenario1` VALUES ('5', 'Schwartz', '79', '1.50', '1.00000', '0', '2012-03-16', null, null, 'ante@adipiscing.org', 'Carla', '1', null, 'there');


-- ----------------------------
-- Table structure for scenario2
-- ----------------------------
DROP TABLE IF EXISTS `scenario2`;
CREATE TABLE "scenario2" (
  "id" bigint(11) unsigned NOT NULL,
  "name" varchar(100) NOT NULL,
  "int" int(11) DEFAULT NULL,
  "currency" decimal(18,2) DEFAULT NULL,
  "float" float(255,5) DEFAULT NULL,
  "bool" tinyint(1) DEFAULT NULL,
  "date" date DEFAULT NULL,
  "datetime" datetime DEFAULT NULL,
  "text" text,
  "email" varchar(100) DEFAULT NULL,
  "varchar_10" varchar(10) NOT NULL,
  "id_detail" int(2) DEFAULT NULL,
  "enum" enum('ok','not_ok') DEFAULT NULL,
  "default" varchar(255) NOT NULL DEFAULT 'FOOBAR',
  PRIMARY KEY ("id")
);

-- ----------------------------
-- Records of scenario2
-- ----------------------------
INSERT INTO `scenario2` VALUES ('0', '', null, null, null, null, null, null, null, null, '', null, null, 'FOOBAR');
INSERT INTO `scenario2` VALUES ('1', 'Andon', '88', '1.32', '1.00000', '1', '2017-05-05', '2017-05-04 00:00:00', 'asdfasdfasdf\r\n\r\n\r\n\r\nadfl;djasfa', 'Nam@quisdiamluctus.org', '1234567890', '1', 'ok', 'FOOBAR');
INSERT INTO `scenario2` VALUES ('2', 'Atkins', '44', '5.55', '1.50000', '0', '0000-00-00', '2017-05-05 12:30:00', null, 'arcu.et.pede@musProin.cam', 'Ishmael', '2', 'not_ok', 'foobar');
INSERT INTO `scenario2` VALUES ('3', 'Hamilton', '10', '10.50', '1.00000', '1', '0000-00-00', null, null, 'dui@duiCras.edu', 'Mohammad', '3', 'ok', 'FOOBAR');
INSERT INTO `scenario2` VALUES ('4', 'Nicholas', '18', '1.92', '2.00000', '0', '0000-00-00', null, null, 'mollis@eutellus.co.uk', 'Troy', '4', null, 'FOOBAR');
INSERT INTO `scenario2` VALUES ('5', 'Schwartz', '79', '1.50', '1.00000', '0', '2012-03-16', null, null, 'ante@adipiscing.org', 'Carla', '1', null, 'there');
