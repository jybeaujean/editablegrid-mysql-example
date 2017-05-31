/*
Navicat PGSQL Data Transfer

Source Server         : reports
Source Server Version : 90218
Source Host           : localhost
Source Database       : demo
Source Schema         : demo

Target Server Type    : PGSQL
Target Server Version : 90218
File Encoding         : 65001

Date: 2017-05-28 20:03:56
*/


-- ----------------------------
-- Table structure for scenario1
-- ----------------------------
DROP TABLE IF EXISTS "demo"."scenario1";
CREATE TABLE "demo"."scenario1" (
"id" serial primary key,
"name" varchar(100) COLLATE "default",
"int" int4,
"currency" numeric(18,2),
"float" float4,
"bool" int2,
"date" date,
"datetime" timestamp(6),
"text" text COLLATE "default",
"email" varchar(100) COLLATE "default",
"varchar_10" varchar(10) COLLATE "default" ,
"id_detail" int4,
"enum" varchar(255) COLLATE "default",
"default" varchar(255) COLLATE "default" 
)
WITH (OIDS=FALSE)

;

-- ----------------------------
-- Records of scenario1
-- ----------------------------
INSERT INTO "demo"."scenario1" VALUES (DEFAULT,'Andon', '88', '1.32', '1', '1', '2017-05-05', '2017-05-04 00:00:00', 'asdfasdfasdfadfl;djasfa', 'Nam@quisdiamluctus.org', '1234567890', '1', 'ok', 'FOOBAR');
INSERT INTO "demo"."scenario1" VALUES (DEFAULT,'Atkins', '44', '5.55', '1.5', '1', '2012-03-16', '2017-05-05 12:30:00', null, 'arcu.et.pede@musProin.cam', 'Ishmael', '2', 'not_ok', 'foobar');
INSERT INTO "demo"."scenario1" VALUES (DEFAULT, 'Hamilton', '10', '10.50', '1', '1', '2012-03-16', null, null, 'dui@duiCras.edu', 'Mohammad', '3', 'ok', 'FOOBAR');
INSERT INTO "demo"."scenario1" VALUES (DEFAULT, 'Nicholas', '18', '1.92', '2', '0', '2012-03-16', null, null, 'mollis@eutellus.co.uk', 'Troy', '4', null, 'FOOBAR');
INSERT INTO "demo"."scenario1" VALUES (DEFAULT, 'Schwartz', '79', '1.50', '1', '0', '2012-03-16', null, null, 'ante@adipiscing.org', 'Carla', '1', null, 'there');




-- ----------------------------
-- Table structure for scenario2
-- ----------------------------
DROP TABLE IF EXISTS "demo"."scenario2";
CREATE TABLE "demo"."scenario2" (
"id" int4 ,
"name" varchar(100) COLLATE "default" ,
"int" int4,
"currency" numeric(18,2),
"float" float4,
"bool" int2,
"date" date,
"datetime" timestamp(6),
"text" text COLLATE "default",
"email" varchar(100) COLLATE "default",
"varchar_10" varchar(10) COLLATE "default" ,
"id_detail" int4,
"enum" varchar(255) COLLATE "default",
"default" varchar(255) COLLATE "default" 
)
WITH (OIDS=FALSE)

;

-- ----------------------------
-- Records of scenario2
-- ----------------------------
INSERT INTO "demo"."scenario2" VALUES ('1', 'Andon', '88', '1.32', '1', '1', '2017-05-05', '2017-05-04 00:00:00', 'asdfasdfasdfadfl;djasfa', 'Nam@quisdiamluctus.org', '1234567890', '1', 'ok', 'FOOBAR');
INSERT INTO "demo"."scenario2" VALUES ('2', 'Atkins', '44', '5.55', '1.5', '1', '2012-03-16', '2017-05-05 12:30:00', null, 'arcu.et.pede@musProin.cam', 'Ishmael', '2', 'not_ok', 'foobar');
INSERT INTO "demo"."scenario2" VALUES ('3', 'Hamilton', '10', '10.50', '1', '1', '2012-03-16', null, null, 'dui@duiCras.edu', 'Mohammad', '3', 'ok', 'FOOBAR');
INSERT INTO "demo"."scenario2" VALUES ('4', 'Nicholas', '18', '1.92', '2', '0', '2012-03-16', null, null, 'mollis@eutellus.co.uk', 'Troy', '4', null, 'FOOBAR');
INSERT INTO "demo"."scenario2" VALUES ('5', 'Schwartz', '79', '1.50', '1', '0', '2012-03-16', null, null, 'ante@adipiscing.org', 'Carla', '1', null, 'there');

-- ----------------------------
-- Alter Sequences Owned By 
-- ----------------------------

-- ----------------------------
-- Primary Key structure for table scenario2
-- ----------------------------
ALTER TABLE "demo"."scenario2" ADD PRIMARY KEY ("id");

