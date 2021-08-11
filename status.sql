INSERT INTO `call_statuses` (`id`, `name`, `value`, `deleted_at`, `created_at`, `updated_at`) VALUES
(10, 'request', 'Request', NULL, '2021-04-28 13:15:15', '2021-04-28 13:15:15'),
(11, 'recieved', 'Recieved', NULL, '2021-04-28 13:15:15', '2021-04-28 13:15:15'),
(12, 'no_active_interpreter_found', 'No active interpreter found', NULL, '2021-04-28 13:15:15', '2021-04-28 13:15:15'),
(13, 'interpreter_found', 'Interpreter found', NULL, '2021-04-28 13:15:15', '2021-04-28 13:15:15'),
(20, 'search', 'Search', NULL, '2021-04-28 13:15:15', '2021-04-28 13:15:15'),
(21, 'messages_created', 'Messages created', NULL, '2021-04-28 13:15:15', '2021-04-28 13:15:15'),
(22, 'message_sent_to_GIQ', 'Message sent to GIQ', NULL, '2021-04-28 13:15:15', '2021-04-28 13:15:15'),
(23, 'refused_by_interpreter', 'Refused by Interpreter', NULL, '2021-04-28 13:15:15', '2021-04-28 13:15:15'),
(24, 'search_time_out', 'Search time out', NULL, '2021-04-28 13:15:15', '2021-04-28 13:15:15'),
(25, 'no_interpreter_found', 'No interpreter found', NULL, '2021-04-28 13:15:15', '2021-04-28 13:15:15'),
(26, 'interpreter_accepted', 'Interpreter accepted', NULL, '2021-04-28 13:15:15', '2021-04-28 13:15:15'),
(30, 'connect', 'Connect', NULL, '2021-04-28 13:15:15', '2021-04-28 13:15:15'),
(31, 'message_created', 'Message created', NULL, '2021-04-28 13:15:15', '2021-04-28 13:15:15'),
(32, 'message_sent_to_supervisor', 'Message sent to supervisor', NULL, '2021-04-28 13:15:15', '2021-04-28 13:15:15'),
(33, 'no_response_from_supervisor', 'No response from supervisor', NULL, '2021-04-28 13:15:15', '2021-04-28 13:15:15'),
(34, 'supervisor_accepted', 'Supervisor accepted', NULL, '2021-04-28 13:15:15', '2021-04-28 13:15:15'),
(35, 'supervisor_canceled', 'Supervisor canceled', NULL, '2021-04-28 13:15:15', '2021-04-28 13:15:15'),
(40, 'call', 'call', NULL, '2021-04-28 13:15:15', '2021-04-28 13:15:15'),
(41, 'handshake_requested', 'Handshake requested', NULL, '2021-04-28 13:15:15', '2021-04-28 13:15:15'),
(42, 'handshake_happened', 'Handshake happened', NULL, '2021-04-28 13:15:15', '2021-04-28 13:15:15'),
(43, 'no_further_status', 'No further status', NULL, '2021-04-28 13:15:15', '2021-04-28 13:15:15'),
(44, 'terminated_by_system_cleaner', 'Terminated by system cleaner', NULL, '2021-04-28 13:15:15', '2021-04-28 13:15:15'),
(50, 'end', 'End', NULL, '2021-04-28 13:15:15', '2021-04-28 13:15:15'),
(51, 'supervisor_disconnected', 'Supervisor disconnected', NULL, '2021-04-28 13:15:15', '2021-04-28 13:15:15'),
(52, 'interpreter_disconnected', 'Interpreter disconnected', NULL, '2021-04-28 13:15:15', '2021-04-28 13:15:15'),
(53, 'supervisor_did_not disconnect', 'Supervisor did not disconnect', NULL, '2021-04-28 13:15:15', '2021-04-28 13:15:15'),
(54, 'interpreter_did_not_disconnect', 'Interpreter did not disconnect', NULL, '2021-04-28 13:15:15', '2021-04-28 13:15:15'),
(55, 'complete_from_both', 'Complete from both', NULL, '2021-04-28 13:15:15', '2021-04-28 13:15:15'),
(56, 'terminated_by_system_process', 'Terminated by system process', NULL, '2021-04-28 13:15:15', '2021-04-28 13:15:15'),
(80, 'terminated_by_system', 'Terminated by system. because No interpreter Found', NULL, '2021-04-28 13:15:15', '2021-04-28 13:15:15');




ALTER TABLE `call_details` ADD `resomn_id` INT(11) NULL DEFAULT NULL AFTER `resolution`;

ALTER TABLE `call_details` ADD `duration` VARCHAR(255) NULL DEFAULT NULL AFTER `end_time`;


CREATE TABLE `call_feedback_users` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `call_id` int(11) DEFAULT NULL,
 `feedback_type` int(11) DEFAULT '1' COMMENT '1 - supervisor feedback, 2 - interpreter feedback',
 `to_user_profile_id` int(11) DEFAULT NULL,
 `to_user_role_id` int(11) DEFAULT NULL,
 `to_user_rating` varchar(255) DEFAULT NULL,
 `disposition_id` varchar(11) DEFAULT NULL,
 `comment` varchar(255) DEFAULT NULL,
 `created_by` int(11) DEFAULT NULL,
 `updated_by` int(11) DEFAULT NULL,
 `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
 `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
 PRIMARY KEY (`id`),
 KEY `call_id` (`call_id`),
 CONSTRAINT `call_feedback_users_ibfk_1` FOREIGN KEY (`call_id`) REFERENCES `calls` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=latin1

CREATE TABLE `call_quality_feedback` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `call_id` int(11) DEFAULT NULL,
 `call_quality_rate` varchar(255) DEFAULT NULL,
 `is_group_call` tinyint(1) NOT NULL DEFAULT '0' COMMENT '0 = Not group call,1 = group call',
 `created_by` int(11) DEFAULT NULL,
 `updated_by` int(11) DEFAULT NULL,
 `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
 `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
 PRIMARY KEY (`id`),
 KEY `call_id` (`call_id`),
 CONSTRAINT `call_quality_feedback_ibfk_1` FOREIGN KEY (`call_id`) REFERENCES `calls` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1


CREATE TABLE `interpreter_breaks` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `user_profile_id` int(11) NOT NULL,
 `break_reason_id` int(11) NOT NULL,
 `status` tinyint(1) NOT NULL DEFAULT '0' COMMENT '1-pending, 2-accept,3-reject ,4-hold',
 `approved_at` datetime DEFAULT NULL,
 `assign_to` int(11) NOT NULL,
 `deleted_at` timestamp NULL DEFAULT NULL,
 `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
 `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 PRIMARY KEY (`id`),
 KEY `user_profile_id` (`user_profile_id`),
 KEY `break_reason_id` (`break_reason_id`),
 KEY `assign_to` (`assign_to`),
 CONSTRAINT `interpreter_breaks_ibfk_1` FOREIGN KEY (`user_profile_id`) REFERENCES `user_profies` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
 CONSTRAINT `interpreter_breaks_ibfk_2` FOREIGN KEY (`break_reason_id`) REFERENCES `break_reasons` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
 CONSTRAINT `interpreter_breaks_ibfk_3` FOREIGN KEY (`assign_to`) REFERENCES `user_profies` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=latin1


CREATE TABLE `interpreter_breaks_logs` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `break_id` int(11) NOT NULL,
 `day` varchar(255) DEFAULT NULL,
 `break_start_time` datetime DEFAULT NULL,
 `break_end_time` datetime DEFAULT NULL,
 `duration` varchar(255) DEFAULT NULL,
 `status` tinyint(1) NOT NULL,
 `deleted_at` timestamp NULL DEFAULT NULL,
 `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
 `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 PRIMARY KEY (`id`),
 KEY `break_id` (`break_id`),
 CONSTRAINT `interpreter_breaks_logs_ibfk_1` FOREIGN KEY (`break_id`) REFERENCES `interpreter_breaks` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=latin1


CREATE TABLE `user_push_devices` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `role_user_id` int(10) unsigned NOT NULL,
 `device_id` text NOT NULL,
 `device_type` varchar(50) NOT NULL COMMENT 'A = Android , I = Iphone',
 `fcm_token` text NOT NULL,
 `deleted_at` timestamp NULL DEFAULT NULL,
 `created_at` datetime NOT NULL,
 `updated_at` datetime DEFAULT NULL,
 PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=latin1 COMMENT='Used to store user device ids to send push notifications'

UPDATE `role_users` SET `role_id` = '4' WHERE `role_users`.`id` = 55;

ALTER TABLE `user_profies`  ADD `avg_user_rating` VARCHAR(255) NULL  AFTER `date_of_birth`;	


ALTER TABLE `interpreter_breaks` ADD `created_by` INT(11) NULL AFTER `status`;


CREATE TABLE `interpreter_breaks_temp` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `user_profile_id` int(11) DEFAULT NULL,
 `break_reason` varchar(255) DEFAULT NULL,
 `break_start_time` datetime DEFAULT NULL,
 `break_end_time` datetime DEFAULT NULL,
 `day` varchar(255) DEFAULT NULL,
 `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
 `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
 PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1




INSERT INTO `users` (`id`, `email`, `password`, `phone`, `login_type`, `authorization_key`, `social_type`, `qb_authorization`, `qb_id`, `qb_password`, `is_active`, `is_forgeted`, `is_verified`, `created_by`, `updated_by`, `is_deleted`, `created_at`, `updated_at`) VALUES (NULL, 'jyoti@mailinator.com', '$2y$10$GVr7FM89BIeaCvLARdM20.CLSBvb3ye9n8IYIJ0t1bROuMtLIbivy', '1234567890', '1', NULL, NULL, NULL, 'signable01', 'signable01', '1', '0', '1', '1', '1', '0', '2021-04-21 05:27:54', '2021-05-06 09:23:07');

INSERT INTO `user_profies` (`id`, `user_id`, `company_id`, `first_name`, `last_name`, `profile_photo`, `gender`, `date_of_join`, `date_of_birth`, `avg_user_rating`, `deleted_at`, `created_at`, `updated_at`) VALUES (NULL, '78', '1', 'Jyoti', 'Patel', 'uploads/users/smne_1619095838.jpg', '2', '2021-06-07', '1998-12-12', NULL, NULL, '2021-04-21 06:21:47', '2021-05-04 10:28:00');

INSERT INTO `user_languages` (`id`, `user_profile_id`, `language_id`, `ranking`, `deleted_at`, `created_at`, `updated_at`) VALUES (NULL, '68', '5', '1', NULL, '2021-04-22 08:53:02', '2021-05-31 13:29:20');
INSERT INTO `user_languages` (`id`, `user_profile_id`, `language_id`, `ranking`, `deleted_at`, `created_at`, `updated_at`) VALUES (NULL, '68', '4', '3', NULL, '2021-04-22 08:53:02', '2021-05-31 13:29:20');
INSERT INTO `user_languages` (`id`, `user_profile_id`, `language_id`, `ranking`, `deleted_at`, `created_at`, `updated_at`) VALUES (NULL, '68', '2', '3', NULL, '2021-04-22 08:53:02', '2021-05-31 13:29:20');
INSERT INTO `user_languages` (`id`, `user_profile_id`, `language_id`, `ranking`, `deleted_at`, `created_at`, `updated_at`) VALUES (NULL, '68', '1', '3', NULL, '2021-04-22 08:53:02', '2021-05-31 13:29:20');


INSERT INTO `locations` (`id`, `user_profile_id`, `city_id`, `miles`, `region`, `site`, `deleted_at`, `created_at`, `updated_at`) VALUES (NULL, '68', '1', '1', '5', 'database', NULL, '2021-04-21 06:27:56', '2021-05-21 07:04:37');


INSERT INTO `role_users` (`id`, `user_profile_id`, `role_id`, `created_at`, `updated_at`) VALUES (NULL, '68', '2', '2021-04-21 06:30:39', '2021-04-21 06:30:39');

INSERT INTO `users` (`id`, `email`, `password`, `phone`, `login_type`, `authorization_key`, `social_type`, `qb_authorization`, `qb_id`, `qb_password`, `is_active`, `is_forgeted`, `is_verified`, `created_by`, `updated_by`, `is_deleted`, `created_at`, `updated_at`) VALUES (NULL, 'priyanka@mailinator.com', '$2y$10$GVr7FM89BIeaCvLARdM20.CLSBvb3ye9n8IYIJ0t1bROuMtLIbivy', '1234567890', '1', NULL, NULL, NULL, 'signable01', 'signable01', '1', '0', '1', '1', '1', '0', '2021-04-21 05:27:54', '2021-05-06 09:23:07');

INSERT INTO `user_profies` (`id`, `user_id`, `company_id`, `first_name`, `last_name`, `profile_photo`, `gender`, `date_of_join`, `date_of_birth`, `avg_user_rating`, `deleted_at`, `created_at`, `updated_at`) VALUES (NULL, '79', '1', 'Priyanka', '', 'uploads/users/smne_1619095838.jpg', '2', '2021-06-07', '1998-01-12', NULL, NULL, '2021-04-21 06:21:47', '2021-06-07 11:09:49');

INSERT INTO `locations` (`id`, `user_profile_id`, `city_id`, `miles`, `region`, `site`, `deleted_at`, `created_at`, `updated_at`) VALUES (NULL, '69', '1', '1', '5', 'database', NULL, '2021-04-21 06:27:56', '2021-05-21 07:04:37');

INSERT INTO `user_languages` (`id`, `user_profile_id`, `language_id`, `ranking`, `deleted_at`, `created_at`, `updated_at`) VALUES (NULL, '69', '3', '1', NULL, '2021-04-22 08:53:02', '2021-05-31 13:29:20');
INSERT INTO `user_languages` (`id`, `user_profile_id`, `language_id`, `ranking`, `deleted_at`, `created_at`, `updated_at`) VALUES (NULL, '69', '2', '3', NULL, '2021-04-22 08:53:02', '2021-05-31 13:29:20');
INSERT INTO `user_languages` (`id`, `user_profile_id`, `language_id`, `ranking`, `deleted_at`, `created_at`, `updated_at`) VALUES (NULL, '69', '1', '3', NULL, '2021-04-22 08:53:02', '2021-05-31 13:29:20');
INSERT INTO `user_languages` (`id`, `user_profile_id`, `language_id`, `ranking`, `deleted_at`, `created_at`, `updated_at`) VALUES (NULL, '69', '4', '3', NULL, '2021-04-22 08:53:02', '2021-05-31 13:29:20');

INSERT INTO `users` (`id`, `email`, `password`, `phone`, `login_type`, `authorization_key`, `social_type`, `qb_authorization`, `qb_id`, `qb_password`, `is_active`, `is_forgeted`, `is_verified`, `created_by`, `updated_by`, `is_deleted`, `created_at`, `updated_at`) VALUES (NULL, 'dekshna@mailinator.com', '$2y$10$GVr7FM89BIeaCvLARdM20.CLSBvb3ye9n8IYIJ0t1bROuMtLIbivy', '1234567890', '1', NULL, NULL, NULL, 'signable01', 'signable01', '1', '0', '1', '1', '1', '0', '2021-04-21 05:27:54', '2021-05-06 09:23:07');

INSERT INTO `user_profies` (`id`, `user_id`, `company_id`, `first_name`, `last_name`, `profile_photo`, `gender`, `date_of_join`, `date_of_birth`, `avg_user_rating`, `deleted_at`, `created_at`, `updated_at`) VALUES (NULL, '80', '1', 'Dekshna', '', 'uploads/users/smne_1619095838.jpg', '2', '2021-06-07', '1998-01-01', NULL, NULL, '2021-04-21 06:21:47', '2021-06-07 11:09:49');

INSERT INTO `locations` (`id`, `user_profile_id`, `city_id`, `miles`, `region`, `site`, `deleted_at`, `created_at`, `updated_at`) VALUES (NULL, '70', '1', '1', '5', 'database', NULL, '2021-04-21 06:27:56', '2021-05-21 07:04:37');

INSERT INTO `role_users` (`id`, `user_profile_id`, `role_id`, `created_at`, `updated_at`) VALUES (NULL, '70', '2', '2021-04-21 06:30:39', '2021-04-21 06:30:39');

INSERT INTO `user_languages` (`id`, `user_profile_id`, `language_id`, `ranking`, `deleted_at`, `created_at`, `updated_at`) VALUES (NULL, '70', '3', '1', NULL, '2021-04-22 08:53:02', '2021-05-31 13:29:20');
INSERT INTO `user_languages` (`id`, `user_profile_id`, `language_id`, `ranking`, `deleted_at`, `created_at`, `updated_at`) VALUES (NULL, '70', '1', '3', NULL, '2021-04-22 08:53:02', '2021-05-31 13:29:20');
INSERT INTO `user_languages` (`id`, `user_profile_id`, `language_id`, `ranking`, `deleted_at`, `created_at`, `updated_at`) VALUES (NULL, '70', '4', '3', NULL, '2021-04-22 08:53:02', '2021-05-31 13:29:20');



INSERT INTO `user_languages` (`id`, `user_profile_id`, `language_id`, `ranking`, `deleted_at`, `created_at`, `updated_at`) VALUES (NULL, '79', '1', '1', NULL, '2021-04-22 08:53:02', '2021-05-31 13:29:20');
INSERT INTO `user_languages` (`id`, `user_profile_id`, `language_id`, `ranking`, `deleted_at`, `created_at`, `updated_at`) VALUES (NULL, '79', '4', '3', NULL, '2021-04-22 08:53:02', '2021-05-31 13:29:20');

UPDATE `role_users` SET `role_id` = '4' WHERE `role_users`.`id` = 57;


CREATE TABLE `tickets` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `category` varchar(11) DEFAULT NULL COMMENT '1-call related, 2-general',
 `assign_user_profile_id` int(11) DEFAULT NULL,
 `assign_role_id` int(11) DEFAULT NULL,
 `subject` varchar(255) DEFAULT NULL,
 `message` varchar(255) DEFAULT NULL,
 `from_user_profile_id` int(11) DEFAULT NULL,
 `from_user_role_id` int(11) DEFAULT NULL,
 `status` int(11) NOT NULL DEFAULT '1' COMMENT '1 = Open, 2 = Assigned 3, = Resolved, 4 = Reopen, 5 = Closed, 6 = Rejected',
 `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
 `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
 `deleted_at` timestamp NULL DEFAULT NULL,
 PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1



CREATE TABLE `ticket_attachments` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `ticket_id` int(11) DEFAULT NULL,
 `attachment_path` varchar(255) DEFAULT NULL,
 `attachment_type` int(11) DEFAULT NULL COMMENT '1-image,2-file ',
 `attachment_name` varchar(255) DEFAULT NULL,
 `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
 `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
 `deleted_at` timestamp NULL DEFAULT NULL,
 PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=latin1


	
CREATE TABLE `ticket_action_attatchments` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `ticket_id` int(11) NOT NULL,
 `action_id` int(11) NOT NULL,
 `attachment_path` varchar(255) NOT NULL,
 `attachment_type` int(11) NOT NULL COMMENT '1-image,2-file',
 `attachment_name` varchar(255) NOT NULL,
 `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
 `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
 `deleted_at` timestamp NULL DEFAULT NULL,
 PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1



CREATE TABLE `ticket_action` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `ticket_id` int(11) DEFAULT NULL,
 `action_type` varchar(255) DEFAULT NULL,
 `action` varchar(255) DEFAULT NULL,
 `action_user_profile_id` int(11) DEFAULT NULL,
 `action_user_role_id` int(11) DEFAULT NULL,
 `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
 `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
 `deleted_at` timestamp NULL DEFAULT NULL,
 PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=latin1


CREATE TABLE `contact_us` (
 `id` int(11) NOT NULL AUTO_INCREMENT,
 `name` varchar(255) DEFAULT NULL,
 `email` varchar(255) DEFAULT NULL,
 `comments` varchar(255) DEFAULT NULL,
 `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
 `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
 PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=latin1



INSERT INTO `email_templates` (`id`, `template_title`, `template_key`, `is_active`, `is_deleted`, `created_by`, `updated_by`, `created_at`, `updated_at`) VALUES (NULL, 'Contact Us', 'contact_us', '1', '0', '1', '1', '2020-02-13 18:57:41', '2020-02-13 13:27:41');


INSERT INTO `email_template_contents` (`id`, `email_template_id`, `language_id`, `email_subject`, `email_body`, `is_deleted`, `created_at`, `updated_at`) VALUES (NULL, '2', '1', 'Contact Us', '&#10;    &#10;    &#10;    &#10;    &#10;    Signable &#10;  &#10;&#10;&#10;    <table width=\"100%\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\" align=\"center\">&#10;      <tbody>&#10;        <tr>&#10;          <td>&#10;            <table cellspacing=\"0\" cellpadding=\"0\" border=\"0\" align=\"center\">&#10;              &#10;              <tbody>&#10;                <tr>&#10;                  <td>&#10;                    <table cellspacing=\"0\" cellpadding=\"0\" border=\"0\">&#10;                      <tbody>&#10;                        <tr>&#10;                          <td width=\"20\"></td>&#10;                          <td>&#10;                            <table class=\"main\" width=\"600\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\">&#10;                              <tbody>&#10;                                <tr>&#10;                                  <td>&#10;                                    <table cellspacing=\"0\" cellpadding=\"0\" border=\"0\">&#10;                                      <tbody>&#10;                                        <tr>&#10;                                          <td>&#10;                                            <table class=\"main\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\">&#10;                                              <tbody>&#10;                                                &#10;                                                <tr>&#10;                                                  <td>&#10;                                                    <table cellspacing=\"0\" cellpadding=\"0\" border=\"0\">&#10;                                                      <tbody>&#10;                                                        <tr><td colspan=\"2\">&#160;</td></tr>&#10;                                                        <tr>&#10;                                                          <td width=\"20\"></td>&#10;                                                          <td><a href=\"{{SITE_URL}}\" target=\"_blank\"><img alt=\"Signable\" src=\"{{LOGO}}\" width=\"177\"> </a></td>&#10;                                                          <td width=\"20\"></td>&#10;                                                        </tr>&#10;                                                        <tr><td colspan=\"2\">&#160;</td></tr>&#10;                                                      </tbody>&#10;                                                    </table>&#10;                                                  </td>&#10;                                                </tr>&#10;                                                <tr>&#10;                                                  <td height=\"13\"></td>&#10;                                                </tr>&#10;                                                <tr>&#10;                                                  <td>&#10;                                                    <table cellspacing=\"0\" cellpadding=\"0\" border=\"0\">&#10;                                                      <tbody>&#10;                                                        <tr>&#10;                                                          <td>&#10;                                                            <table cellspacing=\"0\" cellpadding=\"0\" border=\"0\">&#10;                                                              <tbody>&#10;                                <tr>&#10;                                                                  <td colspan=\"3\" height=\"20\"></td>&#10;                                                                </tr>&#10;                                <tr align=\"center\">&#10;                                                                  <td colspan=\"3\"><strong align=\"center\">Contact Us</strong></td>&#10;                                                                </tr>&#10;                                                                <tr>&#10;                                                                  <td colspan=\"3\" height=\"50\"></td>&#10;                                                                </tr>&#10;                                                                <tr>&#10;                                                                  <td width=\"20\"></td>&#10;                                                                  <td><strong>Dear {{admin_name}},</strong></td>&#10;                                                                  <td width=\"0\"></td>&#10;                                                                </tr>&#10;                                                                <tr>&#10;                                                                  <td colspan=\"3\" height=\"15\"></td>&#10;                                                                </tr>&#10;                                                                <tr>&#10;                                                                  <td width=\"20\"></td>&#10;                                                                  <td>{{user_name}} is  contacting Signable. below details    <td width=\"20\"></td>&#10;                                                                </tr>&#10;                                                                <tr>&#10;                                                                  <td colspan=\"3\" height=\"60\"></td>&#10;                                                                </tr>&#10;       \r\n<tr>&#10;                                                                  <td colspan=\"3\" height=\"50\"></td>&#10;                                                                </tr>&#10;                                                                <tr>&#10;                                                                  <td width=\"20\"></td>&#10;                                                                  <td><strong>User Name: {{user_name}}</strong></td>&#10;                                                                  <td width=\"0\"></td>&#10;                                                                </tr>&#10;\r\n<tr>&#10;                                                                  <td width=\"20\"></td>&#10;                                                                  <td><strong>User Email: {{user_email}}</strong></td>&#10;                                                                  <td width=\"0\"></td>&#10;                                                                </tr>&#10;\r\n<tr>&#10;                                                                  <td width=\"20\"></td>&#10;                                                                  <td><strong>User Comments: {{user_comments}}</strong></td>&#10;                                                                  <td width=\"0\"></td>&#10;                                                                </tr>&#10;\r\n\r\n<tr>&#10;                                                                  <td colspan=\"3\">&#10;                                                                    <table>&#10;                                                                      <tbody>&#10;                                                                        <tr>&#10;                                                                          <td width=\"12\"></td>&#10;                                                                          <td>Thanks &amp; kind regards</td>&#10;                                                                          <td width=\"12\"></td>&#10;                                                                        </tr>&#10;                                                                      </tbody>&#10;                                                                    </table>&#10;                                                                  </td>&#10;                                                                </tr>&#10;                                                                <tr>&#10;                                                                  <td colspan=\"3\">&#10;                                                                    <table>&#10;                                                                      <tbody>&#10;                                                                        <tr>&#10;                                                                          <td width=\"12\"></td>&#10;                                                                          <td valign=\"top\">Your Signable Team</td>&#10;                                                                          <td width=\"12\"></td>&#10;                                                                        </tr>&#10;                                                                      </tbody>&#10;                                                                    </table>&#10;                                                                  </td>&#10;                                                                </tr>&#10;                                                                &#10;                                                                &#10;                                                               &#10;                                                              </tbody>&#10;                                                            </table>&#10;                                                          </td>&#10;                                                          <td></td>&#10;                                                        </tr>&#10;                                                      </tbody>&#10;                                                    </table>&#10;                                                  </td>&#10;                                                </tr>&#10;                                                <tr>&#10;                                                  <td height=\"20\"></td>&#10;                                                </tr>&#10;                                              </tbody>&#10;                                            </table>&#10;                                          </td>&#10;                                        </tr>&#10;                                      </tbody>&#10;                                    </table>&#10;                                  </td>&#10;                                </tr>&#10;                              </tbody>&#10;                            </table>&#10;                          </td>&#10;                          <td width=\"20\"></td>&#10;                        </tr>&#10;                      </tbody>&#10;                    </table>&#10;                  </td>&#10;                </tr>&#10;              </tbody>&#10;            </table>&#10;            <table height=\"16\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\" align=\"center\">&#10;              &#10;              <tbody>&#10;                <tr>&#10;                  <td height=\"16\"></td>&#10;                </tr>&#10;              </tbody>&#10;            </table>&#10;            <table cellspacing=\"0\" cellpadding=\"0\" border=\"0\" align=\"center\">&#10;              &#10;              <tbody>&#10;                <tr>&#10;                  <td>&#10;                    <table cellspacing=\"0\" cellpadding=\"0\" border=\"0\">&#10;                      <tbody>&#10;                        <tr>&#10;                          <td width=\"20\"></td>&#10;                          <td>&#10;                            <table class=\"main\" width=\"600\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\">&#10;                              <tbody>&#10;                                <tr>&#10;                                  <td>&#10;                                    <table width=\"100%;\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\">&#10;                                      <tbody>&#10;                                        <tr>&#10;                                          <td>&#10;                                            <table class=\"main\" width=\"100%\" cellspacing=\"0\" cellpadding=\"0\" border=\"0\">&#10;                                              <tbody>&#10;                                                <tr>&#10;                                                  <td>&#10;                                                    <table cellspacing=\"0\" cellpadding=\"0\" border=\"0\">&#10;                                                      <tbody>&#10;                                                        <tr>&#10;                                                          <td height=\"20\"></td>&#10;                                                        </tr>&#10;                                                        <tr>&#10;                                                          <td colspan=\"3\" valign=\"top\">Download the Signable app!</td>&#10;                                                        </tr>&#10;                                                        <tr>&#10;                                                          <td height=\"20\"></td>&#10;                                                        </tr>&#10;                                                        <tr>&#10;                                                          <td colspan=\"3\" valign=\"top\">Make sure you always have the latest version installed.</td>&#10;                                                        </tr>&#10;                                                        <tr>&#10;                                                          <td height=\"20\"></td>&#10;                                                        </tr>&#10;                                                        <tr>&#10;                                                          <td width=\"20\"></td>&#10;                                                          <td>&#10;                                                            <table cellspacing=\"0\" cellpadding=\"0\" border=\"0\">&#10;                                                              <tbody>&#10;                                                                <tr>&#10;                                                                  <td align=\"right\"><a href=\"{{app_store_link}}\" target=\"_blank\"><img alt=\"App Store\" src=\"{{app_store_logo}}\"> </a></td>&#10;                                                                  <td width=\"20\"></td>&#10;                                                                  <td align=\"left\"><a href=\"{{play_store_link}}\" target=\"_blank\"><img alt=\"Google Play\" src=\"{{play_store_logo}}\"> </a></td>&#10;                                                              </tr></tbody>&#10;                                                            </table>&#10;                                                          </td>&#10;                                                          <td width=\"20\"></td>&#10;                                                        </tr>&#10;                                                        <tr>&#10;                                                          <td colspan=\"3\">&#160;</td>&#10;                                                        </tr>&#10;                                                      </tbody>&#10;                                                    </table>&#10;                                                  </td>&#10;                                                </tr>&#10;                                              </tbody>&#10;                                            </table>&#10;                                          </td>&#10;                                        </tr>&#10;                                      </tbody>&#10;                                    </table>&#10;                                  </td>&#10;                                </tr>&#10;                              </tbody>&#10;                            </table>&#10;                          </td>&#10;                          <td width=\"20\"></td>&#10;                        </tr>&#10;                      </tbody>&#10;                    </table>&#10;                  </td>&#10;                </tr>&#10;              </tbody>&#10;            </table>&#10;            <table cellspacing=\"0\" cellpadding=\"0\" border=\"0\" align=\"center\">&#10;              &#10;              <tbody>&#10;                <tr>&#10;                  <td height=\"10\"></td>&#10;                </tr>&#10;                <tr>&#10;                  <td valign=\"top\">&#169; 2020 Signable - Property Management Software</td>&#10;                </tr>&#10;                <tr>&#10;                  <td height=\"10\"></td>&#10;                </tr>&#10;              </tbody>&#10;            </table>&#10;          </td>&#10;        </tr>&#10;      </tbody>&#10;    </table>&#10;&#10;', '0', '2020-02-13 18:59:30', '2020-09-10 06:40:14');





INSERT INTO `roles` (`id`, `name`, `display_name`, `description`, `deleted_at`, `created_at`) VALUES (NULL, 'qa_manager', 'QA Manager', 'QA Manager', NULL, '2021-04-12 11:53:46');

UPDATE `role_users` SET `role_id` = '5' WHERE `role_users`.`id` = 55;


ALTER TABLE `call_feedback_users` CHANGE `feedback_type` `feedback_type` INT NULL DEFAULT '1' COMMENT '1 - supervisor feedback, 2 - interpreter feedback , 3 QA manager';