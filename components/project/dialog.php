<?php

/*
    *  Copyright (c) Codiad & Kent Safranski (codiad.com), distributed
    *  as-is and without warranty under the MIT License. See
    *  [root]/license.txt for more. This information must remain intact.
    */


require_once('../../common.php');

//////////////////////////////////////////////////////////////////
// Verify Session or Key
//////////////////////////////////////////////////////////////////

checkSession();

switch ($_GET['action']) {

	//////////////////////////////////////////////////////////////
	// List Projects Mini Sidebar
	//////////////////////////////////////////////////////////////
	case 'sidelist':

		// Get access control data
		$projects_assigned = false;
		if (file_exists(BASE_PATH . "/data/" . $_SESSION['user'] . '_acl.php')) {
			$projects_assigned = getJSON($_SESSION['user'] . '_acl.php');
		}

		?>

		<ul>

			<?php

			// Get projects JSON data
			$projects = getJSON('projects.php');
			sort($projects);
			foreach ($projects as $project => $data) {
				$show = true;
				if ($projects_assigned && !in_array($data['path'], $projects_assigned)) {
					$show = false;
				}
				if ($show) {
					if ($_GET['trigger'] == 'true') {
						?>
						<li onclick="codiad.project.open('<?php echo($data['path']); ?>');"><i class="fas fa-archive"></i>
							<?php echo($data['name']); ?></li>

						<?php
					} else {
						?>
						<li ondblclick="codiad.project.open('<?php echo($data['path']); ?>');"><i class="fas fa-archive"></i>
							<?php echo($data['name']); ?></li>

						<?php
					}
				}
			}
			?>

		</ul>

		<?php

		break;

	//////////////////////////////////////////////////////////////
	// List Projects
	//////////////////////////////////////////////////////////////

	case 'list':

		// Get access control data
		$projects_assigned = false;
		if (file_exists(BASE_PATH . "/data/" . $_SESSION['user'] . '_acl.php')) {
			$projects_assigned = getJSON($_SESSION['user'] . '_acl.php');
		}

		?>
		<h1><i class="fas fa-archive"></i><?php i18n("Project List"); ?></h1>

		<form>
			<div id="project-list">
				<table>
					<tr>
						<th class="action"><?php i18n("Open"); ?></th>
						<th><?php i18n("Project Name"); ?></th>
						<th><?php i18n("Path"); ?></th>
						<?php if (checkAccess()) {
							?>
							<th class="action"><?php i18n("Delete"); ?></th>
							<th class="action"><?php i18n("Rename"); ?></th>
							<?php
						} ?>
					</tr>
					<?php

					// Get projects JSON data
					$projects = getJSON('projects.php');
					sort($projects);
					foreach ($projects as $project => $data) {
						$show = true;
						if ($projects_assigned && !in_array($data['path'], $projects_assigned)) {
							$show = false;
						}
						if ($show) {
							?>
							<tr>
								<td class="action"><a onclick="codiad.project.open('<?php echo($data['path']); ?>');" class="fas fa-archive"></a></td>
								<td><?php echo($data['name']); ?></td>
								<td><?php echo($data['path']); ?></td>
								<?php
								if (checkAccess()) {
									if ($_SESSION['project'] == $data['path']) {
										?>
										<td class="action"><a onclick="codiad.toast.error('Active Project Cannot Be Removed');" class="fas fa-ban"></a></td>
										<?php
									} else {
										?>
										<td class="action"><a onclick="codiad.project.delete('<?php echo($data['name']); ?>','<?php echo($data['path']); ?>');" class="fas fa-trash-alt"></a></td>
										<?php
									}
									?>
									<td class="action"><a onclick="codiad.project.rename('<?php echo($data['name']); ?>','<?php echo($data['path']); ?>');" class="fas fa-pencil-alt"></a></td>
									<?php
								}
								?>
							</tr>
							<?php
						}
					}
					?>
				</table>
			</div>
			<?php if (checkAccess()) {
				?><button class="btn-left" onclick="codiad.project.create();"><?php i18n("New Project"); ?></button><?php
			} ?>
		</form>
		<?php

		break;

	//////////////////////////////////////////////////////////////////////
	// Create New Project
	//////////////////////////////////////////////////////////////////////

	case 'create':

		?>
		<form>
			<label><?php i18n("Project Name"); ?></label>
			<input name="project_name" autofocus="autofocus" autocomplete="off">
			<label><?php i18n("Folder Name or Absolute Path"); ?></label>
			<input name="project_path" autofocus="off" autocomplete="off">

			<!-- Clone From GitHub -->
			<div style="width: 500px;">
				<table class="hide" id="git-clone">
					<tr>
						<td>
							<label><?php i18n("Git Repository"); ?></label>
							<input name="git_repo">
						</td>
						<td width="5%">&nbsp;</td>
						<td width="25%">
							<label><?php i18n("Branch"); ?></label>
							<input name="git_branch" value="master">
						</td>
					</tr>
					<tr>
						<td colspan="3" class="note"><?php i18n("Note: This will only work if your Git repo DOES NOT require interactive authentication and your server has git installed."); ?></td>
					</tr>
				</table>
			</div>
			<!-- /Clone From GitHub --><?php
			$action = 'codiad.project.list();';
			if ($_GET['close'] == 'true') {
				$action = 'codiad.modal.unload();';
			}
			?>
			<button class="btn-left"><?php i18n("Create Project"); ?></button>
			<button onclick="$('#git-clone').slideDown(300); $(this).hide(); return false;" class="btn-mid"><?php i18n("...From Git Repo"); ?></button>
			<button class="btn-right" onclick="<?php echo $action; ?>return false;"><?php i18n("Cancel"); ?></button>
		</form>
		<?php
		break;

	//////////////////////////////////////////////////////////////////
	// Rename
	//////////////////////////////////////////////////////////////////
	case 'rename':
		?>
		<form>
			<input type="hidden" name="project_path" value="<?php echo($_GET['path']); ?>">
			<label><i class="fas fa-pencil-alt"></i><?php i18n("Rename Project"); ?></label>
			<input type="text" name="project_name" autofocus="autofocus" autocomplete="off" value="<?php echo($_GET['name']); ?>">
			<button class="btn-left"><?php i18n("Rename"); ?></button>&nbsp;<button class="btn-right" onclick="codiad.modal.unload(); return false;"><?php i18n("Cancel"); ?></button>
		</form>
		<?php
		break;

	//////////////////////////////////////////////////////////////////////
	// Delete Project
	//////////////////////////////////////////////////////////////////////

	case 'delete':

		?>
		<form>
			<input type="hidden" name="project_path" value="<?php echo($_GET['path']); ?>">
			<label><?php i18n("Confirm Project Deletion"); ?></label>
			<pre><?php i18n("Name:"); ?> <?php echo($_GET['name']); ?>, <?php i18n("Path:") ?> <?php echo($_GET['path']); ?></pre>
			<table>
				<tr><td width="5"><input type="checkbox" name="delete" id="delete" value="true"></td><td><?php i18n("Delete Project Files"); ?></td></tr>
				<tr><td width="5"><input type="checkbox" name="follow" id="follow" value="true"></td><td><?php i18n("Follow Symbolic Links "); ?></td></tr>
			</table>
			<button class="btn-left"><?php i18n("Confirm"); ?></button><button class="btn-right" onclick="codiad.project.list();return false;"><?php i18n("Cancel"); ?></button>
		</form>

		<?php
		break;

}

?>