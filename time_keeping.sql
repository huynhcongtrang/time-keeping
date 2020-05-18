/*
 Navicat Premium Data Transfer

 Source Server         : localhost
 Source Server Type    : MySQL
 Source Server Version : 100410
 Source Host           : localhost:3306
 Source Schema         : time_keeping

 Target Server Type    : MySQL
 Target Server Version : 100410
 File Encoding         : 65001

 Date: 19/05/2020 00:04:20
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for access_group
-- ----------------------------
DROP TABLE IF EXISTS `access_group`;
CREATE TABLE `access_group`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `default_page` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL,
  `permission` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 5 CHARACTER SET = utf8 COLLATE = utf8_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of access_group
-- ----------------------------
INSERT INTO `access_group` VALUES (1, 'timing', '2,3,4,9,10');
INSERT INTO `access_group` VALUES (2, NULL, '1,2,3,4,5,6,7,8,9,10');
INSERT INTO `access_group` VALUES (3, NULL, '1,2,3,4,5,6,7,8,9,10,11');

-- ----------------------------
-- Table structure for leave_detail
-- ----------------------------
DROP TABLE IF EXISTS `leave_detail`;
CREATE TABLE `leave_detail`  (
  `id` int(112) NOT NULL AUTO_INCREMENT,
  `staff_id` int(11) NOT NULL,
  `from_date` date NOT NULL,
  `to_date` date NOT NULL,
  `type` int(11) NOT NULL COMMENT '1 : nghi phep, 2 : nghi nua ngay ',
  `type_leave` int(11) NOT NULL COMMENT 'join vao table type_leave',
  `reason` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `status` int(11) NOT NULL COMMENT '0: reject 1 : ok  2 : pedding',
  `approve_by_leader` int(11) NULL DEFAULT NULL,
  `approve_at_leader` datetime(0) NULL DEFAULT NULL,
  `approve_by_hr` int(11) NULL DEFAULT NULL,
  `approve_at_hr` datetime(0) NULL DEFAULT NULL,
  `create_at` datetime(0) NOT NULL,
  `count_day` decimal(11, 1) NOT NULL COMMENT 'Số ngày nghĩ : vì khi đăng kí nghĩ có ngay lể sẽ trừ ra',
  `del` tinyint(3) NULL DEFAULT 0,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 26 CHARACTER SET = utf8 COLLATE = utf8_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of leave_detail
-- ----------------------------
INSERT INTO `leave_detail` VALUES (22, 3, '2020-05-04', '2020-05-06', 1, 23, 'trang tech', 0, 2, '2020-05-16 10:59:01', NULL, NULL, '2020-05-03 18:34:19', 3.0, 0);
INSERT INTO `leave_detail` VALUES (24, 4, '2020-05-13', '2020-05-15', 1, 4, 'test', 1, NULL, NULL, NULL, NULL, '2020-05-12 22:39:29', 3.0, 0);
INSERT INTO `leave_detail` VALUES (25, 3, '2020-05-17', '2020-05-18', 1, 23, 'tra', 1, 2, '2020-05-16 11:20:16', 4, '2020-05-16 11:20:31', '2020-05-16 11:19:46', 1.0, 0);

-- ----------------------------
-- Table structure for leave_time
-- ----------------------------
DROP TABLE IF EXISTS `leave_time`;
CREATE TABLE `leave_time`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `from` date NOT NULL,
  `to` date NOT NULL,
  `status` tinyint(4) NOT NULL COMMENT '1:lock 2:current',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8 COLLATE = utf8_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of leave_time
-- ----------------------------
INSERT INTO `leave_time` VALUES (1, '2020-01-01', '2020-12-31', 2);

-- ----------------------------
-- Table structure for leave_type
-- ----------------------------
DROP TABLE IF EXISTS `leave_type`;
CREATE TABLE `leave_type`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` text CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `status` tinyint(4) NOT NULL DEFAULT 0,
  `parent` int(11) NULL DEFAULT NULL,
  `max_day_per_year` int(11) NULL DEFAULT NULL,
  `max_day_per_time` int(11) NULL DEFAULT NULL,
  `summary` text CHARACTER SET utf8 COLLATE utf8_general_ci NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 24 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of leave_type
-- ----------------------------
INSERT INTO `leave_type` VALUES (1, 'Phép', 1, 0, 0, 0, '');
INSERT INTO `leave_type` VALUES (2, 'Phép năm (tính lương)', 1, 1, 12, 0, '<b>1. Diễn giải:</b> Không.<br>\r\n<b>2. Đối tượng áp dụng:</b> Nhân viên đã ký hợp đồng chính thức với Công ty<br>\r\n<b>3. Hồ sơ yêu cầu cần bổ sung cho Công ty:</b> Không.<br>\r\n<b>4. Lương:</b> Được công ty trả lương những ngày nghỉ<br>');
INSERT INTO `leave_type` VALUES (3, 'Nghỉ việc riêng hưởng lương ', 1, 0, 0, 0, NULL);
INSERT INTO `leave_type` VALUES (4, 'Kết hôn', 1, 3, 0, 3, '<b>1. Diễn giải:</b> Bản thân kết hôn<br>\r\n<b>2. Đối tượng áp dụng:</b> Nhân viên đã ký hợp đồng lao động chính thức với Công ty<br>\r\n<b>3. Hồ sơ yêu cầu cần bổ sung cho Công ty:</b> Yêu cầu upload hình chụp giấy đăng ký kết hôn (Công ty chỉ tính & trả lương khi nhân viên upload hình chụp giấy đăng ký kết hôn lên hệ thống). Nếu không bổ sung hồ sơ hợp lệ, những ngày nghỉ đã đăng ký được tính là nghỉ phép không hưởng lương<br>\r\n<b>4. Lương:</b> Được công ty tính & trả lương 03 ngày nghỉ<br>');
INSERT INTO `leave_type` VALUES (6, 'Nghỉ phép hưởng chế độ bảo hiểm', 1, 0, 0, 0, NULL);
INSERT INTO `leave_type` VALUES (7, 'Con kết hôn', 1, 3, 0, 1, '<b>1. Diễn giải:</b> Con cái kết hôn<br>\r\n<b>2. Đối tượng áp dụng:</b> Nhân viên đã ký hợp đồng lao động chính thức với Công ty<br>\r\n<b>3. Hồ sơ yêu cầu cần bổ sung cho Công ty:</b> Yêu cầu upload hình chụp giấy đăng ký kết hôn của con cái (Công ty chỉ tính & trả lương khi nhân viên upload hình chụp giấy đăng ký kết hôn của con cái lên hệ thống). Nếu không bổ sung hồ sơ hợp lệ, những ngày nghỉ đã đăng ký được tính là nghỉ phép không hưởng lương<br>\r\n<b>4. Lương:</b> Được công ty tính & trả lương 01 ngày nghỉ<br>');
INSERT INTO `leave_type` VALUES (8, 'Bố mẹ/Con mất', 1, 3, 0, 3, '<b>1. Diễn giải:</b> Bộ mẹ (cả bên vợ hoặc chồng), vợ, chồng hoặc con cái mất<br>\r\n<b>2. Đối tượng áp dụng:</b> Nhân viên đã ký hợp đồng lao động chính thức với Công ty<br>\r\n<b>3. Hồ sơ yêu cầu cần bổ sung cho Công ty:</b> Yêu cầu upload hình chụp giấy chứng tử của người mất (Công ty chỉ tính & trả lương khi nhân viên upload hình chụp giấy chứng tử lên hệ thống). Nếu không bổ sung hồ sơ hợp lệ, những ngày nghỉ đã đăng ký được tính là nghỉ phép không hưởng lương<br>\r\n<b>4. Lương:</b> Được công ty tính & trả lương 03 ngày nghỉ<br>');
INSERT INTO `leave_type` VALUES (9, 'Nghỉ ốm đau ngắn ngày', 1, 6, 30, 0, '<b>1. Diễn giải:</b> Bản thân nghỉ ốm đau theo chỉ định của Bác sĩ và được bệnh viện cấp giấy nghỉ hưởng bảo hiểm xã hội (theo mẫu C65) hoặc giấy ra viện trong thời gian nghỉ <br>\r\n<b>2. Đối tượng áp dụng:</b> Nhân viên đang tham gia Bảo hiểm bắt buộc tại Công ty.<b>1. Diễn giải:</b> Bản thân nghỉ ốm đau theo chỉ định của Bác sĩ và được bệnh viện cấp giấy nghỉ hưởng bảo hiểm xã hội (theo mẫu C65) hoặc giấy ra viện trong thời gian nghỉ <br>\r\n<b>2. Đối tượng áp dụng:</b> Nhân viên đang tham gia Bảo hiểm bắt buộc tại Công ty.<br>\r\n<b>3. Hồ sơ yêu cầu cần bổ sung cho Công ty:</b> Yêu cầu gửi Giấy nghỉ hưởng bảo hiểm xã hội (theo mẫu C65)/ giấy ra viện bản chính. Cơ quan BHXH chỉ thanh toán tiền lương các ngày nghỉ khi nhân viên gửi đầy đủ các hồ sơ hợp lệ theo yêu cầu về cho Công Ty.<br>\r\n<b>4. Lương:</b> Cơ quan BHXH tính & trả tiền lương các ngày nghỉ căn cứ trên hồ sơ mà cá nhân nộp lên cho Công ty (tính theo mức lương tham gia Bảo hiểm bắt buộc hàng tháng)<br>\r\n<b>3. Hồ sơ yêu cầu cần bổ sung cho Công ty:</b> Yêu cầu gửi Giấy nghỉ hưởng bảo hiểm xã hội (theo mẫu C65)/ giấy ra viện bản chính. Cơ quan BHXH chỉ thanh toán tiền lương các ngày nghỉ khi nhân viên gửi đầy đủ các hồ sơ hợp lệ theo yêu cầu về cho Công Ty.<br>\r\n<b>4. Lương:</b> Cơ quan BHXH tính & trả tiền lương các ngày nghỉ căn cứ trên hồ sơ mà cá nhân nộp lên cho Công ty (tính theo mức lương tham gia Bảo hiểm bắt buộc hàng tháng)<br>');
INSERT INTO `leave_type` VALUES (10, 'Nghỉ ốm dài ngày', 1, 6, 180, 0, '<b>1. Diễn giải:</b> Chỉ áp dụng đối với các cá nhân mắc các bệnh thuộc danh mục các bệnh cần chữa trị dài ngày do Bộ Y Tế ban hành theo chỉ định của bác sĩ, bệnh viên đăng ký khám chữa bệnh.<br>\r\n<b>2. Đối tượng áp dụng:</b> Nhân viên  đang tham gia Bảo hiểm bắt buộc tại Công ty.<br>\r\n<b>3. Hồ sơ yêu cầu cần bổ sung cho Công ty:</b> Yêu cầu gửi Giấy ra viện (bản chính) đối với trường hợp điều trị nội trú; Biên bản hội chẩn của bệnh viện (bản chính hoặc bản sao có chứng thực và Giấy xác nhận đợt điều trị (bản chính) trú đối với trường hợp điều trị ngoại trú. Cơ quan BHXH chỉ thanh toán tiền lương các ngày nghỉ khi nhân viên gửi đầy đủ các hồ sơ hợp lệ theo yêu cầu về cho Công Ty.<br>\r\n<b>4. Lương:</b> Cơ quan BHXH tính & trả tiền lương các ngày nghỉ căn cứ trên hồ sơ mà cá nhân nộp lên cho Công ty (tính theo mức lương tham gia Bảo hiểm bắt buộc hàng tháng)<br>');
INSERT INTO `leave_type` VALUES (11, 'Nghỉ khi con ốm ', 1, 6, 20, 0, '<b>1. Diễn giải:</b> Nghỉ chăm sóc con ốm, theo chỉ định của Bác sĩ và được bệnh viên cấp giấy ra viện hoặc giấy nghỉ hưởng BHXH .<br>\r\n<b>2. Đối tượng áp dụng:</b> Nhân viên đang tham gia Bảo hiểm bắt buộc tại Công ty.<br>\r\n<b>3. Hồ sơ yêu cầu cần bổ sung cho Công ty:</b> Yêu cầu gửi Giấy nghỉ hưởng bảo hiểm xã hội (theo mẫu C65)/ giấy ra viện bản chính. Cơ quan BHXH chỉ thanh toán tiền lương các ngày nghỉ khi nhân viên gửi đầy đủ các hồ sơ hợp lệ theo yêu cầu về cho Công Ty.<br>\r\n<b>4. Lương:</b> Không được Công ty trả lương những ngày nghỉ, chỉ được Cơ quan bảo hiểm tính & trả tiền chế độ các ngày nghỉ căn cứ trên hồ sơ mà cá nhân nộp lên cho Công ty (tính theo mức lương tham gia Bảo hiểm bắt buộc hàng tháng) <br>');
INSERT INTO `leave_type` VALUES (12, 'Nghỉ khám thai', 1, 6, 0, 1, '<b>1. Diễn giải:</b> Nghỉ đi khám thai theo chỉ định của Bác sĩ và được bệnh viên cấp giấy nghỉ hưởng chế độ BHXH<br>\r\n<b>2. Đối tượng áp dụng:</b> Nhân viên đang tham gia Bảo hiểm bắt buộc tại Công ty<br>\r\n<b>3. Hồ sơ yêu cầu cần bổ sung cho Công ty:</b> Yêu cầu gửi Giấy chứng nhận nghỉ việc hưởng BHXH (bản chính), tổng hợp lại 1 lần và gửi về chung với hồ sơ thai sản sinh con. Cơ quan bảo hiểm xã hội chỉ thanh toán tiền lương các ngày nghỉ khi nhân viên gửi đầy đủ các hồ sơ hợp lệ theo yêu cầu về cho Công Ty.<br>\r\n<b>4. Lương:</b> Không được Công ty trả lương những ngày nghỉ, chỉ được Cơ quan bảo hiểm tính & trả tiền chế độ các ngày nghỉ căn cứ trên hồ sơ mà cá nhân nộp lên cho Công ty (tính theo mức lương tham gia Bảo hiểm bắt buộc hàng tháng)<br>');
INSERT INTO `leave_type` VALUES (13, 'Nghỉ khi bị sẩy thai, nạo hút thai hoặc thai chết lưu', 1, 6, 0, 0, '<b>1. Số ngày nghỉ tối đa:</b><br>\r\nThai < 01 tháng: 10 ngày,<br>\r\n01 tháng - 03 tháng: 20 ngày,<br>\r\n03 tháng - 06 tháng: 40 ngày<br>\r\n> 06 tháng: 50 ngày<br>\r\n<b>2. Diễn giải:</b> Nghỉ chế độ theo chỉ định của Bác sĩ và được bệnh viên cấp giấy ra viện hoặc giấy nghỉ hưởng BHXH .<br>\r\n<b>3. Đối tượng áp dụng:</b> Nhân viên có thời gian tham gia bảo hiểm xã hội từ đủ 6 tháng trở lên trong thời gian 12 tháng trước khi sinh con hoặc nhận con nuôi.<br>\r\n<b>4. Hồ sơ yêu cầu cần bổ sung cho Công ty:</b> Yêu cầu gửi Giấy nghỉ hưởng bảo hiểm xã hội (theo mẫu C65)/ Giấy ra viện bản chính. Cơ quan BHXH chỉ thanh toán tiền lương các ngày nghỉ khi nhân viên gửi đầy đủ các hồ sơ hợp lệ theo yêu cầu về cho Công Ty. Thời gian gửi hồ sơ: không quá 45 ngày kể từ khi ra viện.<br>\r\n<b>5. Lương:</b> Không được Công ty trả lương những ngày nghỉ, chỉ được Cơ quan bảo hiểm tính & trả tiền chế độ các ngày nghỉ căn cứ trên hồ sơ mà cá nhân nộp lên cho Công ty (tính theo mức lương tham gia Bảo hiểm bắt buộc hàng tháng)<br>');
INSERT INTO `leave_type` VALUES (14, 'Nghỉ khi bị sẩy thai (Thai đủ 1 tháng đến dưới 3 tháng)', 0, 6, 0, 20, NULL);
INSERT INTO `leave_type` VALUES (15, 'Nghỉ khi bị sẩy thai (Thai đủ 3 tháng đến dưới 6 tháng)', 0, 6, 0, 40, NULL);
INSERT INTO `leave_type` VALUES (16, 'Nghỉ khi bị sẩy thai (Thai đủ 6 tháng trở lên)', 0, 6, 0, 50, NULL);
INSERT INTO `leave_type` VALUES (17, 'Nghỉ thai sản', 1, 6, 0, 0, '<b>1. Diễn giải:</b> Nghỉ sinh con hưởng chế độ Thai sản theo quy định của Nhà nước<br>\r\n<b>2. Đối tượng áp dụng:</b> Nhân viên có thời gian tham gia bảo hiểm xã hội từ đủ 6 tháng trở lên trong thời gian 12 tháng trước khi sinh con hoặc nhận con nuôi.<br>\r\n<b>3. Hồ sơ yêu cầu cần bổ sung cho Công ty:</b> Yêu cầu gửi Giấy khai sinh /chứng sinh /trích lục giấy khai của con (01 bản sao chứng thực, 01 bản/ 01con). Cơ quan BHXH chỉ thanh toán tiền lương các ngày nghỉ khi nhân viên gửi đầy đủ các hồ sơ hợp lệ theo yêu cầu về cho Công Ty. thời gian gửi hồ sơ: ngay sau khi có đủ giấy tờ và không vượt quá thời gian nghỉ thai sản.<br>\r\n<b>4. Lương:</b> Không được Công ty trả lương những ngày nghỉ, chỉ được cơ quan bảo hiểm tính & trả tiền chế độ (dựa trên mức lương tham gia Bảo hiểm bắt buộc hàng tháng) các ngày nghỉ căn cứ trên hồ sơ mà cá nhân nộp lên cho Công ty<br>');
INSERT INTO `leave_type` VALUES (18, 'Nghỉ chế độ khi vợ sinh con', 1, 6, 0, 7, '<b>1. Diễn giải:</b> Nghỉ khi vợ sinh con theo quy định của Nhà nước<br>\r\n<b>2. Đối tượng áp dụng:</b> Nhân viên đang tham gia Bảo hiểm (BHXH, BHYT, BHTN) bắt buộc tại Công ty<br>\r\n<b>3. Hồ sơ yêu cầu cần bổ sung cho Công ty:</b> Yêu cầu gửi Giấy khai sinh/trích lục giấy khai của con (01 bản sao chứng thực, 01 bản/ 01con) và 01 bản photo CMND của vợ (Cơ quan bảo hiểm xã hội chỉ thanh toán tiền lương các ngày nghỉ khi nhân viên gửi đầy đủ các hồ sơ yêu cầu về cho Công Ty), ghi rõ vợ có hưởng chế độ Thai sản hay không , thời gian gửi hồ sơ: không quá 45 ngày kể từ ngày ra viện. Nếu không bổ sung hồ sơ hợp lệ, những ngày nghỉ đã đăng ký được tính là nghỉ bình thường không được thanh toán tiền chế độ<br>\r\n<b>4. Lương:</b> Không được Công ty trả lương những ngày nghỉ, chỉ được cơ quan bảo hiểm tính & trả tiền chế độ (dựa trên mức lương tham gia Bảo hiểm bắt buộc hàng tháng) các ngày nghỉ căn cứ trên hồ sơ mà cá nhân nộp lên cho Công ty<br>');
INSERT INTO `leave_type` VALUES (19, 'Nghỉ chế độ khi vợ sinh con (Vợ sinh mổ)', 0, 6, 0, 7, 'yêu cầu upload hình Giấy khai sinh của con bản sao, CMND của vợ bản sao');
INSERT INTO `leave_type` VALUES (20, 'Nghỉ dưỡng hồi phục sức khỏe', 1, 6, 0, 5, '<b>1. Diễn giải:</b> Sau khi hết thời gian nghỉ thai sản, nghỉ ốm nếu nhân viên vẫn chưa phục hồi sức khoẻ để đi làm bình thường thì được hưởng thêm chế độ dưỡng sức theo quy định.<br>\r\n<b>2. Đối tượng áp dụng:</b> Nhân viên đang tham gia Bảo hiểm bắt buộc tại Công ty và đã sử dụng hết  thời gian nghỉ ốm hoặc thai sản trước đó.<br>\r\n<b>3. Hồ sơ yêu cầu cần bổ sung cho Công ty:</b> Yêu cầu gửi Giấy chứng nhận nghỉ việc hưởng BHXH (bản chính) hoặc Giấy ra viện bản chính (Cơ quan bảo hiểm xã hội chỉ thanh toán tiền lương các ngày nghỉ khi nhân viên gửi đầy đủ các hồ sơ yêu cầu về cho Công Ty). Nếu không bổ sung hồ sơ hợp lệ, những ngày nghỉ đã đăng ký được tính là nghỉ bình thường không được thanh toán tiền chế độ<br>\r\n<b>4. Lương:</b> Không được Công ty trả lương những ngày nghỉ, chỉ được cơ quan bảo hiểm tính & trả tiền chế độ (dựa trên mức lương tham gia Bảo hiểm bắt buộc hàng tháng) các ngày nghỉ căn cứ trên hồ sơ mà cá nhân nộp lên cho Công ty<br>');
INSERT INTO `leave_type` VALUES (21, 'Nghỉ dưỡng hồi phục sức khỏe (Sinh phẩu thuật)', 0, 6, 0, 7, NULL);
INSERT INTO `leave_type` VALUES (22, 'Nghỉ dưỡng hồi phục sức khỏe (Sinh 2 con trở lên)', 0, 6, 0, 10, NULL);
INSERT INTO `leave_type` VALUES (23, 'Nghỉ không lương', 1, 1, 0, 0, '<b>1. Diễn giải:</b> Nhân viên đã dùng hết phép năm trong 01 chu kỳ và khi không đáp ứng các điều kiện để sử dụng các loại phép còn lại (nghỉ việc riêng hưởng lương, nghỉ phép bảo hiểm).\r\n<b>2. Đối tượng áp dụng:</b> Áp dụng cho tất cả nhân viên có nhu cầu nghỉ việc riêng (ông/ bà mất, nghỉ ốm đau không có chỉ định của bác sĩ và giấy nghỉ hưởng chế độ bảo hiểm, nghỉ khám nghĩa vụ quận sự...)\r\n<b>3. Hồ sơ yêu cầu:</b> Không.\r\n<b>4. Lương:</b> không được hưởng lương các ngày nghỉ<br>');

-- ----------------------------
-- Table structure for menu
-- ----------------------------
DROP TABLE IF EXISTS `menu`;
CREATE TABLE `menu`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL,
  `url` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL,
  `class_icon` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL,
  `order_by` int(11) NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 13 CHARACTER SET = utf8 COLLATE = utf8_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of menu
-- ----------------------------
INSERT INTO `menu` VALUES (1, 'Dashboard', NULL, 'dashboard', 1);
INSERT INTO `menu` VALUES (2, 'My Leave', 'my-leave', 'content_paste', 3);
INSERT INTO `menu` VALUES (3, 'My Check In', 'timing', 'person', 2);
INSERT INTO `menu` VALUES (4, 'Create Leave', 'my-leave/create-leave', 'note_add', 4);
INSERT INTO `menu` VALUES (5, 'List Staff Time', 'time-staff', 'library_books', 5);
INSERT INTO `menu` VALUES (6, 'List Staff Leave', 'leave-staff', 'book', 6);
INSERT INTO `menu` VALUES (7, 'Approve Time/Leave', 'list-approve', 'bubble_chart', 7);
INSERT INTO `menu` VALUES (8, 'Diligent', 'diligent', 'today', 8);
INSERT INTO `menu` VALUES (9, 'Check In Gps', 'time/checkin', '\r\nschedule', 9);
INSERT INTO `menu` VALUES (10, 'Check Out Gps', 'time/checkout', 'schedule', 10);
INSERT INTO `menu` VALUES (11, 'Date Special Setting', 'setting-day', 'settings', 11);

-- ----------------------------
-- Table structure for office
-- ----------------------------
DROP TABLE IF EXISTS `office`;
CREATE TABLE `office`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `long` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `lat` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `address` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8 COLLATE = utf8_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of office
-- ----------------------------
INSERT INTO `office` VALUES (1, 'Vp-ABC', '106.716213', '10.829153', '87 Xuân Diệu, Khánh Hoà');

-- ----------------------------
-- Table structure for reason_reject
-- ----------------------------
DROP TABLE IF EXISTS `reason_reject`;
CREATE TABLE `reason_reject`  (
  `id` int(11) NOT NULL,
  `reason_reject` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `type` tinyint(3) NOT NULL COMMENT '1 : leave',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of reason_reject
-- ----------------------------
INSERT INTO `reason_reject` VALUES (1, 'Không hợp lệ', 1);

-- ----------------------------
-- Table structure for reason_reject_leave
-- ----------------------------
DROP TABLE IF EXISTS `reason_reject_leave`;
CREATE TABLE `reason_reject_leave`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `id_leave` int(11) NOT NULL,
  `id_reason_reject` int(11) NOT NULL,
  `reason` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `create_by` int(11) NOT NULL,
  `create_at` datetime(0) NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 8 CHARACTER SET = utf8 COLLATE = utf8_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of reason_reject_leave
-- ----------------------------
INSERT INTO `reason_reject_leave` VALUES (7, 22, 1, 'sai roi', 4, '2020-05-16 10:59:27');

-- ----------------------------
-- Table structure for reason_staff_temp
-- ----------------------------
DROP TABLE IF EXISTS `reason_staff_temp`;
CREATE TABLE `reason_staff_temp`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `reason` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for reason_time_late
-- ----------------------------
DROP TABLE IF EXISTS `reason_time_late`;
CREATE TABLE `reason_time_late`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `reason_detail` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL,
  `decription` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL,
  `type` int(11) NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for setting_date
-- ----------------------------
DROP TABLE IF EXISTS `setting_date`;
CREATE TABLE `setting_date`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `type` tinyint(3) NOT NULL COMMENT '1: sartuday 2:holiday 3: nghi bat thuong',
  `from` date NOT NULL,
  `to` date NOT NULL,
  `off_date` tinyint(3) NOT NULL COMMENT '1: ngay nghi 0 : ngay k nghi',
  `haft_day` tinyint(3) NOT NULL COMMENT '0 : k phai 1 buổi chiều 2 buổi sáng.',
  `note` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL,
  `created_at` datetime(0) NULL DEFAULT NULL,
  `create_by` int(11) NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 3 CHARACTER SET = utf8 COLLATE = utf8_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of setting_date
-- ----------------------------
INSERT INTO `setting_date` VALUES (1, 1, '2020-04-29', '2020-04-30', 1, 0, NULL, NULL, NULL);
INSERT INTO `setting_date` VALUES (2, 1, '2020-05-07', '2020-05-07', 1, 1, NULL, NULL, NULL);

-- ----------------------------
-- Table structure for shift
-- ----------------------------
DROP TABLE IF EXISTS `shift`;
CREATE TABLE `shift`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `begin` time(0) NULL DEFAULT NULL,
  `begin_break_time` time(0) NULL DEFAULT NULL COMMENT 'neu co',
  `end_break_time` time(0) NULL DEFAULT NULL COMMENT 'neu co',
  `end` time(0) NULL DEFAULT NULL,
  `id_type_shift` int(11) NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8 COLLATE = utf8_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of shift
-- ----------------------------
INSERT INTO `shift` VALUES (1, '08:00:00', '12:00:00', '13:30:00', '17:30:00', 1);

-- ----------------------------
-- Table structure for staff
-- ----------------------------
DROP TABLE IF EXISTS `staff`;
CREATE TABLE `staff`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT 'code staff',
  `full_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `gender` tinyint(5) NOT NULL,
  `birth_day` datetime(0) NOT NULL,
  `phone` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `cmnd` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `team_id` int(10) NOT NULL,
  `title_id` int(10) NOT NULL,
  `join_at` date NOT NULL,
  `department_id` int(11) NOT NULL,
  `disible` int(11) NOT NULL,
  `id_office` int(11) NOT NULL,
  `is_leader` tinyint(3) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 5 CHARACTER SET = utf8 COLLATE = utf8_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of staff
-- ----------------------------
INSERT INTO `staff` VALUES (1, 'A123', 'Admin', 1, '2020-04-01 21:09:23', '0326646934', '225762841', 0, 0, '0000-00-00', 0, 0, 1, 0);
INSERT INTO `staff` VALUES (2, 'A345', 'Leader', 0, '2020-04-01 21:09:23', '0326646934', '225762841', 2, 7, '0000-00-00', 1, 0, 1, 1);
INSERT INTO `staff` VALUES (3, 'A567', 'Nhan vien A', 1, '2020-04-01 21:09:23', '0326646934', '225762841', 2, 3, '0000-00-00', 1, 0, 1, 0);
INSERT INTO `staff` VALUES (4, 'A321', 'Hr', 0, '2020-04-01 21:09:23', '0326646934', '225762841', 5, 6, '0000-00-00', 4, 0, 1, 0);

-- ----------------------------
-- Table structure for staff_contract
-- ----------------------------
DROP TABLE IF EXISTS `staff_contract`;
CREATE TABLE `staff_contract`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `staff_id` int(11) NOT NULL,
  `contract_term` int(11) NOT NULL COMMENT 'dieu khoan hop dong',
  `from` date NULL DEFAULT NULL,
  `to` date NULL DEFAULT NULL,
  `create_at` date NOT NULL,
  `create_by` int(11) NOT NULL,
  `type_contract` int(11) NOT NULL,
  `status` int(11) NOT NULL COMMENT '1 : đang ap dụng 0 : --',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 3 CHARACTER SET = utf8 COLLATE = utf8_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of staff_contract
-- ----------------------------
INSERT INTO `staff_contract` VALUES (1, 1, 1, '2020-03-02', NULL, '2020-04-13', 1, 1, 1);

-- ----------------------------
-- Table structure for staff_priviledge
-- ----------------------------
DROP TABLE IF EXISTS `staff_priviledge`;
CREATE TABLE `staff_priviledge`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `staff_id` int(11) NOT NULL,
  `access` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL,
  `default_page` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL,
  `menu` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for staff_shift
-- ----------------------------
DROP TABLE IF EXISTS `staff_shift`;
CREATE TABLE `staff_shift`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `staff_id` int(11) NOT NULL,
  `shift_work_id` int(11) NOT NULL,
  `from` date NOT NULL COMMENT 'time lúc gán nhân viên',
  `to` date NULL DEFAULT NULL COMMENT 'time khi nhân viên kết thúc ca làm việc và chuyển sang ca khác',
  `status` tinyint(4) NOT NULL COMMENT 'trạng thái : 1 : đang áp dụng , 0 .del',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8 COLLATE = utf8_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of staff_shift
-- ----------------------------
INSERT INTO `staff_shift` VALUES (1, 1, 1, '2020-04-19', '0000-00-00', 1);

-- ----------------------------
-- Table structure for staff_temp
-- ----------------------------
DROP TABLE IF EXISTS `staff_temp`;
CREATE TABLE `staff_temp`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `staff_id` int(11) NOT NULL,
  `total_date_off` int(11) NOT NULL,
  `status` int(11) NOT NULL,
  `reson_id` int(11) NOT NULL,
  `add_reason` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL,
  `created_at` datetime(0) NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1 CHARACTER SET = utf8 COLLATE = utf8_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for team
-- ----------------------------
DROP TABLE IF EXISTS `team`;
CREATE TABLE `team`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `parent_id` int(11) NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 8 CHARACTER SET = utf8 COLLATE = utf8_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of team
-- ----------------------------
INSERT INTO `team` VALUES (1, 'Technology', 0);
INSERT INTO `team` VALUES (2, 'Tech', 1);
INSERT INTO `team` VALUES (3, 'PHP Dev', 2);
INSERT INTO `team` VALUES (4, 'HR', 0);
INSERT INTO `team` VALUES (5, 'HR', 4);
INSERT INTO `team` VALUES (6, 'AD executive', 5);
INSERT INTO `team` VALUES (7, 'Leader Tech', 2);

-- ----------------------------
-- Table structure for time_gps
-- ----------------------------
DROP TABLE IF EXISTS `time_gps`;
CREATE TABLE `time_gps`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `staff_id` int(11) NOT NULL,
  `day` date NOT NULL COMMENT 'day check',
  `check_at` time(0) NULL DEFAULT NULL COMMENT 'time check',
  `long` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `lat` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `img1` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `img2` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `type` tinyint(3) NOT NULL COMMENT '1 : check in 2: check out',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 14 CHARACTER SET = utf8 COLLATE = utf8_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of time_gps
-- ----------------------------
INSERT INTO `time_gps` VALUES (10, 1, '2020-04-20', '10:21:00', '109.153252', '12.049567', 'image_1_1_1587396062.png', 'image_2_1_1587396062.png', 1);
INSERT INTO `time_gps` VALUES (11, 1, '2020-04-20', '10:21:00', '109.15325199999998', '12.049567', 'image_1_1_1587396108.png', 'image_2_1_1587396108.png', 2);

-- ----------------------------
-- Table structure for time_late
-- ----------------------------
DROP TABLE IF EXISTS `time_late`;
CREATE TABLE `time_late`  (
  `id` int(11) NOT NULL,
  `staff_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `status` tinyint(4) NOT NULL,
  `reason_id` int(11) NOT NULL,
  `add_reason` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL,
  `approved_by` int(11) NOT NULL,
  `approved_at` datetime(0) NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB CHARACTER SET = utf8 COLLATE = utf8_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Table structure for type_shift
-- ----------------------------
DROP TABLE IF EXISTS `type_shift`;
CREATE TABLE `type_shift`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `decription` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL DEFAULT NULL,
  `status` tinyint(3) NOT NULL COMMENT '1 : dang ap dung, 0 del',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 2 CHARACTER SET = utf8 COLLATE = utf8_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of type_shift
-- ----------------------------
INSERT INTO `type_shift` VALUES (1, 'Hành Chính', 'ca hành chính này cho nhân viên làm ở office', 1);

-- ----------------------------
-- Table structure for user
-- ----------------------------
DROP TABLE IF EXISTS `user`;
CREATE TABLE `user`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `password` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL,
  `permission_id` int(11) NOT NULL,
  `staff_id` int(11) NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 5 CHARACTER SET = utf8 COLLATE = utf8_unicode_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of user
-- ----------------------------
INSERT INTO `user` VALUES (1, 'admin@gmail.com', 'e10adc3949ba59abbe56e057f20f883e', 3, 1);
INSERT INTO `user` VALUES (2, 'leader@gmail.com', 'e10adc3949ba59abbe56e057f20f883e', 2, 2);
INSERT INTO `user` VALUES (3, 'staff@gmail.com', 'e10adc3949ba59abbe56e057f20f883e', 1, 3);
INSERT INTO `user` VALUES (4, 'hr@gmail.com', 'e10adc3949ba59abbe56e057f20f883e', 2, 4);

-- ----------------------------
-- Event structure for check_leave_approve
-- ----------------------------
DROP EVENT IF EXISTS `check_leave_approve`;
delimiter ;;
CREATE EVENT `check_leave_approve`
ON SCHEDULE
EVERY ''0 1'' DAY_HOUR STARTS '2020-05-01 10:51:52'
DO UPDATE
leave_detail l  SET l.`status` = 0
WHERE MONTH(l.to_date) < MONTH (NOW())
AND l.`status` = 2
;;
delimiter ;

SET FOREIGN_KEY_CHECKS = 1;
