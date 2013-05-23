DROP TABLE IF EXISTS
`jos_xaccess_system`,
`jos_xaccounts`,
`jos_xagents`,
`jos_xbalance_sheet`,
`jos_xbank_holidays`,
`jos_xbranch`,
`jos_xcategory`,
`jos_xclosings`,
`jos_xconfig`,
`jos_xdealer`,
`jos_xdocuments`,
`jos_xdocuments_submitted`,
`jos_xevents`,
`jos_xitems`,
`jos_xlog`,
`jos_xmember`,
`jos_xpremiums`,
`jos_xreports`,
`jos_xschemes`,
`jos_xstaff`,
`jos_xstaff_attendance`,
`jos_xstaff_details`,
`jos_xstaff_payments`,
`jos_xstock`,
`jos_xstock_log`,
`jos_xtemp`,
`jos_xtemp_loan_accounts`,
`jos_xtemp_saving_accounts`,
`jos_xtemp_share_accounts`,
`jos_xtransactions`,
`jos_xtransaction_type`,
`jos_xxyz`;

-- ----------------------------
-- Records of jos_users
-- ----------------------------
 DELETE FROM jos_users where `name` = 'DFL Default' and username = 'xadmin';

-- ----------------------------
-- Records of jos_core_acl_aro
-- ----------------------------


-- ----------------------------
-- Records of jos_core_acl_groups_aro_map
-- ----------------------------