<?php include "templates/include/header.php" ?>
<?php include "templates/admin/include/header.php" ?>

        <h1><?php echo $results['pageTitle']?></h1>

        <form action="admin.php?action=<?php echo $results['formAction']?>" method="post">
            <input type="hidden" name="userId" value="<?php echo $results['user']->id ?>">

    <?php if ( isset( $results['errorMessage'] ) ) { ?>
            <div class="errorMessage"><?php echo $results['errorMessage'] ?></div>
    <?php } ?>

            <ul>

              <li>
                <label for="login">User Login</label>
                <input type="text" name="login" id="login" placeholder="User login" required autofocus maxlength="255" value="<?php echo htmlspecialchars( $results['user']->login ?? '' )?>" />
              </li>

              <li>
                <label for="password">Password</label>
                <input type="password" name="password" id="password" placeholder="<?php echo $results['user']->id ? 'Leave empty to keep current password' : 'Enter password' ?>" maxlength="255" />
                <?php if ($results['user']->id) { ?>
                    <small style="display: block; color: #666; margin-top: 5px;">Оставьте пустым, чтобы не менять пароль</small>
                <?php } ?>
              </li>

              <li>
                <label for="isActive">User Status</label>
                <input type="checkbox" name="isActive" id="isActive" value="1" <?php echo (isset($results['user']->isActive) && $results['user']->isActive == 1) ? "checked" : "" ?> />
                <label for="isActive" style="display: inline; font-weight: normal;">Active (can login)</label>
              </li>

            </ul>

            <div class="buttons">
              <input type="submit" name="saveChanges" value="Save Changes" />
              <input type="submit" formnovalidate name="cancel" value="Cancel" />
            </div>

        </form>

    <?php if ($results['user']->id) { ?>
          <p><a href="admin.php?action=deleteUser&amp;userId=<?php echo $results['user']->id ?>" onclick="return confirm('Delete This User?')">
                  Delete This User
              </a>
          </p>
    <?php } ?>
	  
<?php include "templates/include/footer.php" ?>

