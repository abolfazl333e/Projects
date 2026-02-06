<?php if($notice): ?>
    <div class="notice notice-success"><p><?php echo esc_html($notice); ?></p></div>
<?php endif; ?>

<div class="manage-group-container">

    <h2>مدیریت گروه‌ها و اعضا (کلاس: <?php echo esc_html($active_class); ?>)</h2>

    <!-- فرم افزودن گروه -->
    <div class="manage-group-card">
        <h3>افزودن گروه جدید</h3>
        <form method="post" enctype="multipart/form-data">
            <input type="text" name="name" placeholder="نام گروه" required>
            <input type="number" name="group_number" placeholder="شماره گروه" min="1" required>
            <select name="type">
                <option value="ورزشی">ورزشی</option>
                <option value="درسی">درسی</option>
            </select>
            <!-- نمایش کلاس فعال -->
            <input type="text" value="<?php echo esc_attr($active_class); ?>" readonly>
            <input type="hidden" name="class" value="<?php echo esc_attr($active_class); ?>">
            <input type="file" name="image" accept="image/*">
            <button type="submit" name="add_group">افزودن گروه</button>
        </form>
    </div>

    <!-- فرم ویرایش گروه -->
    <?php if($edit_group): ?>
        <div class="manage-group-card" id="manage-group-edit-card">
            <span class="manage-group-close-btn" id="manage-group-close-edit">×</span>
            <h3>ویرایش گروه: <?php echo esc_html($edit_group['name']); ?></h3>

            <form method="post" enctype="multipart/form-data">
                <input type="hidden" name="group_id" value="<?php echo $edit_group['id']; ?>">
                <input type="text" name="name" value="<?php echo esc_html($edit_group['name']); ?>" required>
                <input type="number" name="group_number" value="<?php echo esc_html($edit_group['group_number']); ?>" min="1" required>
                <select name="type">
                    <option value="ورزشی" <?php selected($edit_group['type'], 'ورزشی'); ?>>ورزشی</option>
                    <option value="درسی" <?php selected($edit_group['type'], 'درسی'); ?>>درسی</option>
                </select>
                <!-- نمایش کلاس فعال -->
                <input type="text" value="<?php echo esc_attr($active_class); ?>" readonly>
                <input type="hidden" name="class" value="<?php echo esc_attr($active_class); ?>">
                <?php if($edit_group['image']): ?>
                    <img src="<?php echo esc_url($edit_group['image']); ?>" class="group-img">
                <?php endif; ?>
                <input type="file" name="image" accept="image/*">
                <input type="hidden" name="current_image" value="<?php echo esc_url($edit_group['image']); ?>">
                <button type="submit" name="edit_group">ذخیره تغییرات</button>
            </form>

            <!-- اعضای گروه -->
            <h4>اعضای گروه</h4>
            <button id="manage-group-remove-selected">حذف اعضای انتخاب شده</button>
            <ul id="manage-group-member-list">
                <?php
                $members = $wpdb->get_results(
                    $wpdb->prepare(
                        "SELECT sg.id as sg_id, s.name 
                         FROM {$wpdb->prefix}student_group sg 
                         JOIN {$wpdb->prefix}students s ON sg.student_id = s.id 
                         WHERE sg.group_id = %d AND s.class=%s
                         ORDER BY s.name",
                        $edit_group['id'],
                        $active_class
                    ),
                    ARRAY_A
                );
                foreach($members as $m): ?>
                    <li data-sg-id="<?php echo $m['sg_id']; ?>">
                        <input type="checkbox" class="member-checkbox"> <?php echo esc_html($m['name']); ?>
                    </li>
                <?php endforeach; ?>
            </ul>

            <!-- افزودن دانش‌آموز به گروه -->
            <h4>افزودن دانش‌آموز به گروه</h4>
            <form method="post">
                <input type="hidden" name="group_id" value="<?php echo $edit_group['id']; ?>">
                <select name="student_ids[]" multiple class="select-multi" required size="5">
                    <?php
                    $students_in_class = $wpdb->get_results(
                        $wpdb->prepare("SELECT * FROM {$wpdb->prefix}students WHERE class=%s ORDER BY name", $active_class),
                        ARRAY_A
                    );
                    foreach($students_in_class as $s): ?>
                        <option value="<?php echo $s['id']; ?>"><?php echo esc_html($s['name']); ?></option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" name="assign_multiple">افزودن</button>
            </form>
        </div>
    <?php endif; ?>

    <!-- لیست گروه‌ها -->
    <h3>لیست گروه‌ها</h3>
    <table>
        <tr>
            <th>شماره گروه</th>
            <th>نام گروه</th>
            <th>نوع گروه</th>
            <th>کلاس</th>
            <th>عکس گروه</th>
            <th>اعضا</th>
            <th>عملیات</th>
        </tr>

        <?php foreach($groups as $g):
            $members = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT s.name 
                     FROM {$wpdb->prefix}student_group sg 
                     JOIN {$wpdb->prefix}students s ON sg.student_id = s.id 
                     WHERE sg.group_id = %d AND s.class=%s
                     ORDER BY s.name",
                    $g['id'],
                    $active_class
                ),
                ARRAY_A
            );
            ?>
            <tr>
                <td><?php echo esc_html($g['group_number']); ?></td>
                <td><?php echo esc_html($g['name']); ?></td>
                <td><?php echo esc_html($g['type']); ?></td>
                <td><?php echo esc_html($active_class); ?></td>
                <td><?php if($g['image']): ?><img src="<?php echo esc_url($g['image']); ?>" class="group-img"><?php endif; ?></td>
                <td>
                    <ul>
                        <?php foreach($members as $m) echo "<li>".esc_html($m['name'])."</li>"; ?>
                    </ul>
                </td>
                <td>
                    <a href="?page=myclass_manage_groups&edit_group=<?php echo $g['id']; ?>">ویرایش</a> |
                    <a href="?page=myclass_manage_groups&delete_group=<?php echo $g['id']; ?>" onclick="return confirm('آیا مطمئن هستید؟')">حذف</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</div>
