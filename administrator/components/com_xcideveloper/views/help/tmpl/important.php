<style type="text/css" media="screen">
	code {
		white-space: pre;
	}
</style>

<!-- START NAVIGATION -->
<div id="masthead">
<table cellpadding="0" cellspacing="0" border="0" style="width:100%">
<tr>
<td><h1>xCIDEVELOPER IMPORTANT WORK FLOWS</h1></td>
<td id="breadcrumb_right"><a href="../../toc.html"> </a></td>
</tr>
</table>
</div>
<!-- END NAVIGATION -->


<!-- START BREADCRUMB -->
<table cellpadding="0" cellspacing="0" border="0" style="width:100%">
<tr>
<td id="breadcrumb">

</td>
<td id="searchbox"></td>
</tr>
</table>
<!-- END BREADCRUMB -->

<br clear="all" />


<!-- START CONTENT -->
<div id="content">

<h2>Few Very Special and must Notice point about xCIDeveloper</h2>

    <p><strong>* --Yes, you can manage to work with Joomla's menu system in joomla style but that restricts you to single controller and takes you away from CI method*. </strong><br />
    My solution to this condition is to define all my components menu as external url in following style :<code>index.php?option=com_{yourcomponent}&task={yourController}.{yourFunction}</code> This way you can utilise complete CI methods.    </p>
    <h2>*Explanation why:</h2>
    <p> Because joomla distributes working on the base of layouts in menu which are similer to views in CI but. In CI work is distributed in terms of controllers and functions. So either you work with default controller only and go for</p>
<code> switch($this->input->get('layout')) </code>
    <p>for distributing the task (Thats not good programming) or just use external urls for your generated component and work in pure CI way.
    </p>
    <p class="important"><strong>* --This xCIDeveloper can be installed in joomla 1.5, 1.6 or even in 1.7 in same way and components developed with xCI can also work in any joomla as far as you don't use any joomla version specific features and config files are maintained for J1.5 and 1.6 and above both.</strong></p>
    <p> There is just a simple change required in config files. insted of params and param tag in config there should be fieldset and field tag for Joomla 1.6 and above and fieldset should have attribute name and lable. This can be more understand my making a component and study its config.xml file in administrator/your_component directory.
    </p>
    <p>* --We are working to make your components developed in xCI to work in joomla menu system till then mention your external urls for various menus available in your components help file or any where you think it should be.
    --instead of redirect of CI you have to use xRedirect in the following manner <code>xRedirect('index.php?option=com_{yourcomponent}&task={yourController}.{yourFunction}','Any Message to display on next redirected page [optional]','info/error [option]')</code> </p>
</div>