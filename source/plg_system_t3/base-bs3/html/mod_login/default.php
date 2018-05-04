<?php
/**
 * @package     Joomla.Site
 * @subpackage  mod_login
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// no direct access
defined('_JEXEC') or die;

JLoader::register('UsersHelperRoute', JPATH_SITE . '/components/com_users/helpers/route.php');

JHtml::_('behavior.keepalive');
if (version_compare(JVERSION, '3.0', 'ge')) {
	JHtml::_('bootstrap.tooltip');
}
?>
<?php if ($type == 'logout') : ?>
	<form action="<?php echo JRoute::_('index.php', true, $params->get('usesecure')); ?>" method="post" id="login-form"
		  class="form-vertical">
		<?php if ($params->get('greeting')) : ?>
			<div class="login-greeting">
				<?php if ($params->get('name') == 0) : {
					echo JText::sprintf('MOD_LOGIN_HINAME', htmlspecialchars($user->get('name')));
				} else : {
					echo JText::sprintf('MOD_LOGIN_HINAME', htmlspecialchars($user->get('username')));
				} endif; ?>
			</div>
		<?php endif; ?>
		<div class="logout-button">
			<input type="submit" name="Submit" class="btn btn-primary" value="<?php echo JText::_('JLOGOUT'); ?>"/>
			<input type="hidden" name="option" value="com_users"/>
			<input type="hidden" name="task" value="user.logout"/>
			<input type="hidden" name="return" value="<?php echo $return; ?>"/>
			<?php echo JHtml::_('form.token'); ?>
		</div>
	</form>
<?php else : ?>
	<form action="<?php echo JRoute::_('index.php', true, $params->get('usesecure')); ?>" method="post" id="login-form">
		<?php if ($params->get('pretext')): ?>
			<div class="pretext">
				<p><?php echo $params->get('pretext'); ?></p>
			</div>
		<?php endif; ?>
		<fieldset class="userdata">
			<div id="form-login-username" class="form-group">
				<?php if (!$params->get('usetext')) : ?>
					<div class="input-group">
						<span class="input-group-addon">
							<i class="fa fa-user tip" title="<?php echo JText::_('MOD_LOGIN_VALUE_USERNAME') ?>"></i>
						</span>
						<input id="modlgn-username" type="text" name="username" class="input form-control" tabindex="0" size="18"
							   placeholder="<?php echo JText::_('MOD_LOGIN_VALUE_USERNAME') ?>" aria-label="username" />
					</div>
				<?php else: ?>
					<label for="modlgn-username"><?php echo JText::_('MOD_LOGIN_VALUE_USERNAME') ?></label>
					<input id="modlgn-username" type="text" name="username" class="input-sm form-control" tabindex="0"
						   size="18" placeholder="<?php echo JText::_('MOD_LOGIN_VALUE_USERNAME') ?>"/>
				<?php endif; ?>
			</div>
			<div id="form-login-password" class="form-group">
				<?php if (!$params->get('usetext')) : ?>
				<div class="input-group">
						<span class="input-group-addon">
							<i class="fa fa-lock tip" title="<?php echo JText::_('JGLOBAL_PASSWORD') ?>"></i>
						</span>
					<input id="modlgn-passwd" type="password" name="password" class="input form-control" tabindex="0"
						   size="18" placeholder="<?php echo JText::_('JGLOBAL_PASSWORD') ?>" aria-label="password" />
				</div>
			<?php else: ?>
				<label for="modlgn-passwd"><?php echo JText::_('JGLOBAL_PASSWORD') ?></label>
				<input id="modlgn-passwd" type="password" name="password" class="input-sm form-control" tabindex="0"
					   size="18" placeholder="<?php echo JText::_('JGLOBAL_PASSWORD') ?>"/>
			<?php endif; ?>
			</div>
			<?php if (isset($twofactormethods) && count($twofactormethods) > 1): ?>
			<div id="form-login-secretkey" class="form-group">
				<?php if (!$params->get('usetext')) : ?>
				<div class="input-group">
					<span class="input-group-addon">
						<i class="fa fa-star hasTooltip" title="<?php echo JText::_('JGLOBAL_SECRETKEY'); ?>"></i>
					</span>
					<label for="modlgn-secretkey" class="element-invisible"><?php echo JText::_('JGLOBAL_SECRETKEY'); ?></label>
					<input id="modlgn-secretkey" type="text" name="secretkey" class="input form-control" tabindex="0" size="18" placeholder="<?php echo JText::_('JGLOBAL_SECRETKEY') ?>" />
					<span class="input-group-addon hasTooltip" title="<?php echo JText::_('JGLOBAL_SECRETKEY_HELP'); ?>">
						<i class="fa fa-question-circle"></i>
					</span>
				</div>
				<?php else: ?>
					<label for="modlgn-secretkey"><?php echo JText::_('JGLOBAL_SECRETKEY') ?></label>
					<input id="modlgn-secretkey" type="text" name="secretkey" class="input-small" tabindex="0" size="18" placeholder="<?php echo JText::_('JGLOBAL_SECRETKEY') ?>" />
					<span class="btn btn-default width-auto hasTooltip" title="<?php echo JText::_('JGLOBAL_SECRETKEY_HELP'); ?>">
						<i class="fa fa-question-circle"></i>
					</span>
				<?php endif; ?>
			</div>
			<?php endif; ?>
		
			<?php if (JPluginHelper::isEnabled('system', 'remember')) : ?>
				<div id="form-login-remember" class="form-group">
					<div class="checkbox">
						<input id="modlgn-remember" type="checkbox"
							name="remember" class="input"
							value="yes" aria-label="remember"/> <?php echo JText::_('MOD_LOGIN_REMEMBER_ME') ?>
					</div>
				</div>
			<?php endif; ?>
			<div class="control-group">
				<input type="submit" name="Submit" class="btn btn-primary" value="<?php echo JText::_('JLOGIN') ?>"/>
			</div>

			<?php $usersConfig = JComponentHelper::getParams('com_users'); ?>
			<ul class="unstyled">
				<?php if ($usersConfig->get('allowUserRegistration')) : ?>
				<li>
					<a href="<?php echo JRoute::_('index.php?option=com_users&view=registration'); ?>">
						<?php echo JText::_('MOD_LOGIN_REGISTER'); ?> <span class="fa fa-arrow-right"></span></a>
				</li>
				<?php endif; ?>
				<li>
					<a href="<?php echo JRoute::_('index.php?option=com_users&view=remind'); ?>">
						<?php echo JText::_('MOD_LOGIN_FORGOT_YOUR_USERNAME'); ?></a>
				</li>
				<li>
					<a href="<?php echo JRoute::_('index.php?option=com_users&view=reset'); ?>"><?php echo JText::_('MOD_LOGIN_FORGOT_YOUR_PASSWORD'); ?></a>
				</li>
			</ul>

			<input type="hidden" name="option" value="com_users"/>
			<input type="hidden" name="task" value="user.login"/>
			<input type="hidden" name="return" value="<?php echo $return; ?>"/>
			<?php echo JHtml::_('form.token'); ?>
		</fieldset>
		<?php if ($params->get('posttext')): ?>
			<div class="posttext">
				<p><?php echo $params->get('posttext'); ?></p>
			</div>
		<?php endif; ?>
	</form>
<?php endif; ?>
