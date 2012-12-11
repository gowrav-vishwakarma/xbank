<?php
/**
 * @copyright	Copyright (C) 2005 - 2010 Open Source Matters. All rights reserved.
 * @license		GNU/GPL, see LICENSE.php
 * Joomla! is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 * See COPYRIGHT.php for copyright notices and details.
 */
// no direct access
defined('_JEXEC') or die('Restricted access');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $this->language; ?>" lang="<?php echo $this->language; ?>" dir="<?php echo $this->direction; ?>" id="minwidth" >
    <head>
        <jdoc:include type="head" />

        <link rel="stylesheet" href="templates/system/css/system.css" type="text/css" />
        <link href="templates/<?php echo $this->template ?>/css/template.css" rel="stylesheet" type="text/css" />

<?php if ($this->direction == 'rtl') : ?>
            <link href="templates/<?php echo $this->template ?>/css/template_rtl.css" rel="stylesheet" type="text/css" />
<?php endif; ?>

        <!--[if IE 7]>
        <link href="templates/<?php echo $this->template ?>/css/ie7.css" rel="stylesheet" type="text/css" />
        <![endif]-->

        <!--[if lte IE 6]>
        <link href="templates/<?php echo $this->template ?>/css/ie6.css" rel="stylesheet" type="text/css" />
        <![endif]-->

<?php if ($this->params->get('useRoundedCorners')) : ?>
        <link rel="stylesheet" type="text/css" href="templates/<?php echo $this->template ?>/css/rounded.css" />
<?php else : ?>
            <link rel="stylesheet" type="text/css" href="templates/<?php echo $this->template ?>/css/norounded.css" />
<?php endif; ?>

<?php if (JModuleHelper::isEnabled('menu')) : ?>
                <script type="text/javascript" src="templates/<?php echo $this->template ?>/js/menu.js"></script>
                <script type="text/javascript" src="templates/<?php echo $this->template ?>/js/index.js"></script>
<?php endif; ?>

            </head>
            <body id="minwidth-body">

		<?php
                if(JFactory::getUser()->gid < 24){
            ?>


                <!-- flooble sidebar menu start -->
                <script language="javascript">
                    // Floating Sidebar Menu Script from Flooble.com
                    // For more information, visit
                    //	http://www.flooble.com/scripts/sidebar.php
                    // Copyright 2003 Animus Pactum Consulting inc.
                    //---------------------------------------------------------
                    var ie = false;
                    var open = true;
                    var oldwidth = -1;
                    if (document.all) { ie = true; }

                    function getObj(id) {
                        if (ie) { return document.all[id]; }
                        else {    return document.getElementById(id);    }
                    }

                    function toggleSidebar() {
                        var sidebar = getObj('sidebarcontents');
                        var menu = getObj('sidebarmenu');
                        var arrow = getObj('sidearrow');
                        if (open) {
                            var sidec = getObj('sidebar');
                            var h = sidec.scrollHeight;
                            if (oldwidth < 0) {
                                oldwidth = sidebar.scrollWidth;
                            }
                            sidebar.style.display = 'none';
                            td = getObj('sidebartd');
                            td.style.width = 0;
                            arrow.innerHTML = '>';
                            //alert(h + ' - ' + sidec.scrollHeight);
                            sidec.style.height = h;
                            open = false;
                        } else {
                            sidebar.style.display = 'block';
                            sidebar.style.width = oldwidth;
                            arrow.innerHTML = '<';
                            open = true;
                        }
                        getObj('focuser').focus();

                    }

                    function setSidebarTop() {
                        //alert('hoy');
                        var sidec = getObj('sidebar');
                        sidec.style.top = 10 + document.body.scrollTop;
                        setTimeout('setSidebarTop()', 10);
                    }

                    setTimeout('setSidebarTop();', 2000);

                </script>
                <table border="0" cellspacing="0" cellpadding="3" id="sidebar" bgcolor="#99FFFF"
                       style="border-top: 1px solid #003300; border-bottom: 1px solid #003300;
                       position:absolute; z-index:100; right:0px; top:150px;
                       font-family:Verdana;">
                    <tr>

                        <td width="25" valign="top" align="center" bgcolor="#003300" id="menucontainer">
                            <a href="javascript:void(0);" id="sidebarmenu" onClick="toggleSidebar();"
                               style="color:#FFFFFF; text-decoration:none;font-weight:bold;  font-family:Helvetica;"><span
                                    id="sidearrow">&lt;</span><br/>M<br/>e<br/>n<br/>u</a><br/>
                            <a href="javascript:void(0);" style="color: #003300; heigh:1px;"
                               id="focuser"> </a>
                        </td>


                        <td valign="top" id="sidebartd">
                            <div id="sidebarcontents" style="padding: 15px;">

                                <li><a href="./index.php?option=<?php echo JRequest::getVar("option") ?>">DashBoard</a></li>
                                <li>Accounts
                                    <ul>
                                        <li><a href="./index.php?option=<?php echo JRequest::getVar("option") ?>&task=accounts_cont.index">Accounts</a></li>
                                        <li><a href="./index.php?option=<?php echo JRequest::getVar("option") ?>&task=accounts_cont.NewAccountForm">Create New Account</a></li>
                                        <li><a href="./index.php?option=<?php echo JRequest::getVar("option") ?>&task=search_cont.searchAccountForm">Search Account</a></li>
                                    </ul>
                                </li>

                                <li><a href="./index.php?option=<?php echo JRequest::getVar("option") ?>&task=setdate_cont.setDateTimeForm">Set Date</a></li>
                                <li><a href="./index.php?option=<?php echo JRequest::getVar("option") ?>&task=schemes_cont.dashboard">Schemes</a></li>
                                <li>Reports
                                    <ul>
                                        <li><a href="./index.php?option=<?php echo JRequest::getVar("option") ?>&task=report_cont.balanceSheetForm">Balance Sheet</a></li>
                                        <li><a href="./index.php?option=<?php echo JRequest::getVar("option") ?>&task=report_cont.pandlForm">P & L Account</a></li>
                                        <li><a href="./index.php?option=<?php echo JRequest::getVar("option") ?>&task=report_cont.dayBookForm">Day Book</a></li>
<li><a href="./index.php?option=<?php echo JRequest::getVar("option") ?>&task=report_cont.cashBookForm">Cash Book</a></li>
                                        <li><a href="./index.php?option=<?php echo JRequest::getVar("option") ?>&task=report_cont.accountstatementform">Account Statement</a></li>
                                    </ul>
                                </li>
                                <li>Transactions
                                    <ul>
                                        <li><a href="./index.php?option=<?php echo JRequest::getVar("option") ?>&task=transaction_cont.deposit">Deposit</a></li>
                                        <li><a href="./index.php?option=<?php echo JRequest::getVar("option") ?>&task=transaction_cont.withdrawl">Withdraw</a></li>
                                        <li><a href="./index.php?option=<?php echo JRequest::getVar("option") ?>&task=transaction_cont.jv">JV</a></li>
                                    </ul>
                                </li>

                            </div>
                        </td>

                    </tr>
                </table>
                <script language="Javascript">toggleSidebar();</script>
                <!-- flooble sidebar menu end -->




		 <?php
                }
                ?>






                <div id="border-top" class="<?php echo $this->params->get('headerColor', 'green'); ?>">
                    <div>
                        <div>
                            <span class="version"><b><?php $session =& JFactory::getSession(); echo  ($session->get('currdate')? date("d M, Y", strtotime($session->get('currdate'))) : date("d M, Y"));  ?></b><?php// echo JText::_('Version') ?> <?php// echo JVERSION; ?></span>
                            <span class="title"><?php echo ($this->params->get('showSiteName') ? $mainframe->getCfg('sitename') : JText::_('Administration')) . (JFactory::getUser() ? " - " . JFactory::getUser()->name . " (" . JFactory::getUser()->username . ")" : ""); ?></span>
                        </div>
                    </div>
                </div>
                <div id="header-box">
                    <div id="module-status">
                        <jdoc:include type="modules" name="status"  />
                    </div>
                    <div id="module-menu">
                        <jdoc:include type="modules" name="menu" />
                    </div>
                    <div class="clr"></div>
                </div>
                <div id="content-box">
                    <div class="border">
                        <div class="padding">
                            <div id="toolbar-box">
                                <div class="t">
                                    <div class="t">
                                        <div class="t"></div>
                                    </div>
                                </div>
                                <div class="m">
                                    <jdoc:include type="modules" name="toolbar" />
                                    <jdoc:include type="modules" name="title" />
                                    <div class="clr"></div>
                                </div>
                                <div class="b">
                                    <div class="b">
                                        <div class="b"></div>
                                    </div>
                                </div>
                            </div>
                            <div class="clr"></div>
<?php if (!JRequest::getInt('hidemainmenu')): ?>
                                <jdoc:include type="modules" name="submenu" style="rounded"  id="submenu-box" />
<?php endif; ?>
                    <jdoc:include type="message" />
                    <div id="element-box">
                        <div class="t">
                            <div class="t">
                                <div class="t"></div>
                            </div>
                        </div>
                        <div class="m">
                            <jdoc:include type="component" />
                            <div class="clr"></div>
                        </div>
                        <div class="b">
                            <div class="b">
                                <div class="b"></div>
                            </div>
                        </div>
                    </div>
                    <noscript>
<?php echo JText::_('WARNJAVASCRIPT') ?>
                    </noscript>
                    <div class="clr"></div>
                </div>
                <div class="clr"></div>
            </div>
        </div>
        <div id="border-bottom"><div><div></div></div></div>
        <div id="footer">
            <p class="copyright">
                Developed By : <a href="http://www.xavoc.com" target="_blank">Xavoc International</a>
<?php // echo  JText::_('ISFREESOFTWARE')  ?>
            </p>
        </div>
    </body>
</html>
