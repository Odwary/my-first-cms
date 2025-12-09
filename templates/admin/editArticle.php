<?php include "templates/include/header.php" ?>
<?php include "templates/admin/include/header.php" ?>
<!--        <?php echo "<pre>";
            print_r($results);
            print_r($data);
        echo "<pre>"; ?> Данные о массиве $results и типе формы передаются корректно-->

        <h1><?php echo $results['pageTitle']?></h1>

        <form action="admin.php?action=<?php echo $results['formAction']?>" method="post">
            <input type="hidden" name="articleId" value="<?php echo $results['article']->id ?>">

    <?php if ( isset( $results['errorMessage'] ) ) { ?>
            <div class="errorMessage"><?php echo $results['errorMessage'] ?></div>
    <?php } ?>
    
    <?php if ( isset( $results['errors'] ) && is_array( $results['errors'] ) && !empty( $results['errors'] ) ) { ?>
            <div class="errorMessage">
                <?php foreach ( $results['errors'] as $error ) { ?>
                    <div><?php echo htmlspecialchars( $error ) ?></div>
                <?php } ?>
            </div>
    <?php } ?>

            <ul>

              <li>
                <label for="title">Article Title</label>
                <input type="text" name="title" id="title" placeholder="Name of the article" required autofocus maxlength="255" value="<?php echo htmlspecialchars( $results['article']->title )?>" />
              </li>

              <li>
                <label for="summary">Article Summary</label>
                <textarea name="summary" id="summary" placeholder="Brief description of the article" required maxlength="1000" style="height: 5em;"><?php echo htmlspecialchars( $results['article']->summary )?></textarea>
              </li>

              <li>
                <label for="content">Article Content</label>
                <textarea name="content" id="content" placeholder="The HTML content of the article" required maxlength="100000" style="height: 30em;"><?php echo htmlspecialchars( $results['article']->content )?></textarea>
              </li>

              <li>
                <label for="categoryId">Article Category</label>
                <select name="categoryId" id="categoryId">
                  <option value="0"<?php echo !$results['article']->categoryId ? " selected" : ""?>>(none)</option>
                <?php foreach ( $results['categories'] as $category ) { ?>
                  <option value="<?php echo $category->id?>"<?php echo ( $category->id == $results['article']->categoryId ) ? " selected" : ""?>><?php echo htmlspecialchars( $category->name )?></option>
                <?php } ?>
                </select>
              </li>

              <li>
                <label for="subcategoryId">Article Subcategory</label>
                <select name="subcategoryId" id="subcategoryId">
                  <option value="0"<?php echo !$results['article']->subcategoryId ? " selected" : ""?>>(none)</option>
                <?php 
                // Группируем подкатегории по категориям
                if (isset($results['subcategoriesByCategory'])) {
                    foreach ( $results['subcategoriesByCategory'] as $catId => $subcategories ) {
                        if (isset($results['categories'][$catId])) {
                            echo '<optgroup label="' . htmlspecialchars($results['categories'][$catId]->name) . '">';
                            foreach ( $subcategories as $subcategory ) {
                                $selected = ( $subcategory->id == $results['article']->subcategoryId ) ? " selected" : "";
                                echo '<option value="' . $subcategory->id . '"' . $selected . '>' . htmlspecialchars( $subcategory->name ) . '</option>';
                            }
                            echo '</optgroup>';
                        }
                    }
                }
                ?>
                </select>
              </li>

              <li>
                <label for="publicationDate">Publication Date</label>
                <input type="date" name="publicationDate" id="publicationDate" placeholder="YYYY-MM-DD" required maxlength="10" value="<?php echo $results['article']->publicationDate ? date( "Y-m-d", $results['article']->publicationDate ) : "" ?>" />
              </li>

              <li>
                <label for="isActive">Article Status</label>
                <input type="checkbox" name="isActive" id="isActive" value="1" <?php echo (isset($results['article']->isActive) && $results['article']->isActive == 1) ? "checked" : "" ?> />
                <label for="isActive" style="display: inline; font-weight: normal;">Active (visible on site)</label>
              </li>

              <li>
                <label for="authorIds">Article Authors</label>
                <select name="authorIds[]" id="authorIds" multiple size="5" style="min-height: 100px;">
                <?php 
                $selectedAuthorIds = isset($results['article']->id) ? $results['article']->getAuthorIds() : array();
                if (isset($results['users'])) {
                    foreach ( $results['users'] as $user ) { 
                        $selected = in_array($user->id, $selectedAuthorIds) ? " selected" : "";
                        echo '<option value="' . $user->id . '"' . $selected . '>' . htmlspecialchars( $user->login ) . '</option>';
                    }
                }
                ?>
                </select>
                <small style="display: block; color: #666; margin-top: 5px;">Удерживайте Ctrl для выбора нескольких авторов</small>
              </li>


            </ul>

            <div class="buttons">
              <input type="submit" name="saveChanges" value="Save Changes" />
              <input type="submit" formnovalidate name="cancel" value="Cancel" />
            </div>

        </form>

    <?php if ($results['article']->id) { ?>
          <p><a href="admin.php?action=deleteArticle&amp;articleId=<?php echo $results['article']->id ?>" onclick="return confirm('Delete This Article?')">
                  Delete This Article
              </a>
          </p>
    <?php } ?>
	  
<?php include "templates/include/footer.php" ?>

              