<?php
/**
 * 求人応募ページのテンプレート
 * Template Name: 求人応募ページ
 */
get_header();

// ログインチェック
if (!is_user_logged_in()) {
    // ログインしていない場合、ログインページにリダイレクト
    wp_redirect(home_url('/login/?redirect_to=' . urlencode($_SERVER['REQUEST_URI'])));
    exit;
}

// URLパラメータから求人IDを取得
$job_id = isset($_GET['job_id']) ? intval($_GET['job_id']) : 0;
if (empty($job_id)) {
    // URLから求人IDを抽出（/apply/123/ のような形式）
    $request_uri = $_SERVER['REQUEST_URI'];
    $matches = array();
    if (preg_match('/\/apply\/(\d+)/', $request_uri, $matches)) {
        $job_id = intval($matches[1]);
    }
}

// 求人情報の取得
$job = get_post($job_id);

if (!$job || $job->post_type !== 'job' || $job->post_status !== 'publish') {
    // 求人が存在しない、または公開されていない場合
    ?>
    <div class="content">
        <div class="container">
            <div class="error-message">
                <h1>求人が見つかりません</h1>
                <p>お探しの求人は存在しないか、すでに募集が終了しています。</p>
                <a href="<?php echo home_url('/'); ?>" class="back-button">トップページへ戻る</a>
            </div>
        </div>
    </div>
    <?php
    get_footer();
    exit;
}

// 求人情報の取得
$job_title = $job->post_title;
$company_name = get_post_meta($job_id, '_company_name', true);
$position = '';
$positions = get_the_terms($job_id, 'job_position');
if (!empty($positions) && !is_wp_error($positions)) {
    $position = $positions[0]->name;
}

// 現在のユーザー情報を取得
$current_user = wp_get_current_user();
$user_first_name = get_user_meta($current_user->ID, 'first_name', true);
$user_last_name = get_user_meta($current_user->ID, 'last_name', true);
$user_email = $current_user->user_email;
$user_phone = get_user_meta($current_user->ID, 'phone', true);
$user_address = get_user_meta($current_user->ID, 'address', true);

// 応募フォームの送信処理
$form_submitted = false;
$form_errors = array();
$form_success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'apply_job') {
    $form_submitted = true;
    
    // バリデーション
    $last_name = isset($_POST['last_name']) ? sanitize_text_field($_POST['last_name']) : '';
    $first_name = isset($_POST['first_name']) ? sanitize_text_field($_POST['first_name']) : '';
    $last_name_kana = isset($_POST['last_name_kana']) ? sanitize_text_field($_POST['last_name_kana']) : '';
    $first_name_kana = isset($_POST['first_name_kana']) ? sanitize_text_field($_POST['first_name_kana']) : '';
    $email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
    $phone = isset($_POST['phone']) ? sanitize_text_field($_POST['phone']) : '';
    $postal_code = isset($_POST['postal_code']) ? sanitize_text_field($_POST['postal_code']) : '';
    $prefecture = isset($_POST['prefecture']) ? sanitize_text_field($_POST['prefecture']) : '';
    $city = isset($_POST['city']) ? sanitize_text_field($_POST['city']) : '';
    $street_address = isset($_POST['street_address']) ? sanitize_text_field($_POST['street_address']) : '';
    $building = isset($_POST['building']) ? sanitize_text_field($_POST['building']) : '';
    $birth_year = isset($_POST['birth_year']) ? intval($_POST['birth_year']) : 0;
    $birth_month = isset($_POST['birth_month']) ? intval($_POST['birth_month']) : 0;
    $birth_day = isset($_POST['birth_day']) ? intval($_POST['birth_day']) : 0;
    $gender = isset($_POST['gender']) ? sanitize_text_field($_POST['gender']) : '';
    $employment_status = isset($_POST['employment_status']) ? sanitize_text_field($_POST['employment_status']) : '';
    $education = isset($_POST['education']) ? sanitize_text_field($_POST['education']) : '';
    $qualification = isset($_POST['qualification']) ? sanitize_textarea_field($_POST['qualification']) : '';
    $work_experience = isset($_POST['work_experience']) ? sanitize_textarea_field($_POST['work_experience']) : '';
    $motivation = isset($_POST['motivation']) ? sanitize_textarea_field($_POST['motivation']) : '';
    $desired_salary = isset($_POST['desired_salary']) ? sanitize_text_field($_POST['desired_salary']) : '';
    $desired_working_hours = isset($_POST['desired_working_hours']) ? sanitize_text_field($_POST['desired_working_hours']) : '';
    $resume_file = isset($_FILES['resume_file']) ? $_FILES['resume_file'] : array();
    
    // 必須項目チェック
    if (empty($last_name)) {
        $form_errors[] = '姓を入力してください。';
    }
    if (empty($first_name)) {
        $form_errors[] = '名を入力してください。';
    }
    if (empty($email)) {
        $form_errors[] = 'メールアドレスを入力してください。';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $form_errors[] = '有効なメールアドレスを入力してください。';
    }
    if (empty($phone)) {
        $form_errors[] = '電話番号を入力してください。';
    }
    if (empty($postal_code) || empty($prefecture) || empty($city) || empty($street_address)) {
        $form_errors[] = '住所を入力してください。';
    }
    if (empty($birth_year) || empty($birth_month) || empty($birth_day)) {
        $form_errors[] = '生年月日を入力してください。';
    }
    if (empty($gender)) {
        $form_errors[] = '性別を選択してください。';
    }
    if (empty($motivation)) {
        $form_errors[] = '志望動機を入力してください。';
    }
    
    // ファイルアップロードの処理
    $uploaded_file_path = '';
    if (!empty($resume_file['name'])) {
        $upload_dir = wp_upload_dir();
        $resume_dir = $upload_dir['basedir'] . '/resumes/' . $current_user->ID;
        
        // ディレクトリが存在しない場合は作成
        if (!file_exists($resume_dir)) {
            wp_mkdir_p($resume_dir);
        }
        
        $file_name = sanitize_file_name($resume_file['name']);
        $uploaded_file_path = $resume_dir . '/' . $file_name;
        
        // ファイル移動
        $file_uploaded = move_uploaded_file($resume_file['tmp_name'], $uploaded_file_path);
        
        if (!$file_uploaded) {
            $form_errors[] = 'レジュメのアップロードに失敗しました。もう一度お試しください。';
        }
    }
    
    // エラーがなければ応募処理
    if (empty($form_errors)) {
        // 応募データの保存（カスタム投稿タイプ「application」に保存）
        $application_data = array(
            'post_title'    => $last_name . ' ' . $first_name . ' - ' . $job_title,
            'post_type'     => 'application',
            'post_status'   => 'publish',
            'post_author'   => $current_user->ID,
        );
        
        $application_id = wp_insert_post($application_data);
        
        if ($application_id) {
            // メタデータの保存
            add_post_meta($application_id, '_job_id', $job_id);
            add_post_meta($application_id, '_last_name', $last_name);
            add_post_meta($application_id, '_first_name', $first_name);
            add_post_meta($application_id, '_last_name_kana', $last_name_kana);
            add_post_meta($application_id, '_first_name_kana', $first_name_kana);
            add_post_meta($application_id, '_email', $email);
            add_post_meta($application_id, '_phone', $phone);
            add_post_meta($application_id, '_postal_code', $postal_code);
            add_post_meta($application_id, '_prefecture', $prefecture);
            add_post_meta($application_id, '_city', $city);
            add_post_meta($application_id, '_street_address', $street_address);
            add_post_meta($application_id, '_building', $building);
            add_post_meta($application_id, '_birth_date', sprintf('%04d-%02d-%02d', $birth_year, $birth_month, $birth_day));
            add_post_meta($application_id, '_gender', $gender);
            add_post_meta($application_id, '_employment_status', $employment_status);
            add_post_meta($application_id, '_education', $education);
            add_post_meta($application_id, '_qualification', $qualification);
            add_post_meta($application_id, '_work_experience', $work_experience);
            add_post_meta($application_id, '_motivation', $motivation);
            add_post_meta($application_id, '_desired_salary', $desired_salary);
            add_post_meta($application_id, '_desired_working_hours', $desired_working_hours);
            add_post_meta($application_id, '_resume_file_path', $uploaded_file_path);
            add_post_meta($application_id, '_application_status', 'pending'); // 応募ステータス（pending, reviewed, interviewing, hired, rejected）
            
            // ユーザープロフィールを更新（次回の応募に備えて）
            update_user_meta($current_user->ID, 'first_name', $first_name);
            update_user_meta($current_user->ID, 'last_name', $last_name);
            if (!empty($phone)) {
                update_user_meta($current_user->ID, 'phone', $phone);
            }
            if (!empty($postal_code) && !empty($prefecture) && !empty($city) && !empty($street_address)) {
                $address = $postal_code . ' ' . $prefecture . $city . $street_address;
                if (!empty($building)) {
                    $address .= ' ' . $building;
                }
                update_user_meta($current_user->ID, 'address', $address);
            }
            
            // 応募完了のメール送信
            // 応募者へのメール
            $to = $email;
            $subject = '【こどもプラス】応募を受け付けました - ' . $job_title;
            
            $message = "{$last_name} {$first_name} 様\n\n";
            $message .= "こどもプラス求人サイトをご利用いただき、ありがとうございます。\n\n";
            $message .= "以下の求人への応募を受け付けました。\n\n";
            $message .= "求人: {$job_title}\n";
            $message .= "会社名: {$company_name}\n";
            if (!empty($position)) {
                $message .= "職種: {$position}\n";
            }
            $message .= "\n";
            $message .= "応募内容を確認後、担当者より連絡させていただきます。\n";
            $message .= "しばらくお待ちください。\n\n";
            $message .= "※このメールは自動送信されています。返信はできませんのでご了承ください。\n\n";
            $message .= "-------------------------------------\n";
            $message .= "こどもプラス求人サイト\n";
            $message .= home_url() . "\n";
            $message .= "-------------------------------------\n";
            
            $headers = array('From: こどもプラス求人サイト <no-reply@example.com>');
            
            wp_mail($to, $subject, $message, $headers);
            
            // 採用担当者へのメール
            // ※管理者メールアドレスまたは設定された通知先へ送信
            $admin_email = get_option('admin_email');
            // ACFで設定された通知先があれば上書き
            if (function_exists('get_field') && get_field('notification_email', 'option')) {
                $admin_email = get_field('notification_email', 'option');
            }
            
            $subject_admin = '【こどもプラス】新しい応募がありました - ' . $job_title;
            
            $message_admin = "新しい応募がありました。\n\n";
            $message_admin .= "求人: {$job_title}\n";
            $message_admin .= "応募者: {$last_name} {$first_name}\n";
            $message_admin .= "メール: {$email}\n";
            $message_admin .= "電話番号: {$phone}\n\n";
            $message_admin .= "応募詳細は管理画面からご確認ください。\n";
            $message_admin .= admin_url('edit.php?post_type=application') . "\n\n";
            $message_admin .= "-------------------------------------\n";
            $message_admin .= "こどもプラス求人サイト\n";
            $message_admin .= home_url() . "\n";
            $message_admin .= "-------------------------------------\n";
            
            wp_mail($admin_email, $subject_admin, $message_admin, $headers);
            
            $form_success = true;
        } else {
            $form_errors[] = '応募情報の保存に失敗しました。もう一度お試しください。';
        }
    }
}
?>

<div class="content">
    <div class="container">
        <?php if ($form_success): ?>
        <!-- 応募完了メッセージ -->
        <div class="application-complete">
            <div class="application-complete-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <h1 class="application-complete-title">応募が完了しました</h1>
            <p class="application-complete-message">
                <?php echo esc_html($job_title); ?>への応募を受け付けました。<br>
                応募内容を確認後、担当者より連絡させていただきます。
            </p>
            <div class="application-complete-actions">
                <a href="<?php echo home_url('/'); ?>" class="back-to-home">トップページに戻る</a>
                <a href="<?php echo home_url('/members/applications/'); ?>" class="view-applications">応募履歴を見る</a>
            </div>
        </div>
        <?php else: ?>
        <!-- 求人応募フォーム -->
        <div class="apply-container">
            <div class="apply-header">
                <h1 class="apply-title">応募フォーム</h1>
                <div class="apply-job-info">
                    <div class="apply-job-title"><?php echo esc_html($job_title); ?></div>
                    <?php if (!empty($company_name)): ?>
                        <div class="apply-job-company"><?php echo esc_html($company_name); ?></div>
                    <?php endif; ?>
                    <?php if (!empty($position)): ?>
                        <div class="apply-job-position"><?php echo esc_html($position); ?></div>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ($form_submitted && !empty($form_errors)): ?>
            <div class="apply-errors">
                <div class="error-heading">入力内容に問題があります</div>
                <ul class="error-list">
                    <?php foreach ($form_errors as $error): ?>
                        <li><?php echo esc_html($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php endif; ?>

            <div class="apply-form">
                <form action="" method="post" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="apply_job">
                    <input type="hidden" name="job_id" value="<?php echo $job_id; ?>">

                    <section class="form-section">
                        <h2 class="section-title">基本情報</h2>
                        
                        <div class="form-row">
                            <div class="form-group half">
                                <label for="last_name">姓<span class="required">*</span></label>
                                <input type="text" id="last_name" name="last_name" value="<?php echo isset($_POST['last_name']) ? esc_attr($_POST['last_name']) : esc_attr($user_last_name); ?>" required>
                            </div>
                            <div class="form-group half">
                                <label for="first_name">名<span class="required">*</span></label>
                                <input type="text" id="first_name" name="first_name" value="<?php echo isset($_POST['first_name']) ? esc_attr($_POST['first_name']) : esc_attr($user_first_name); ?>" required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group half">
                                <label for="last_name_kana">姓（フリガナ）</label>
                                <input type="text" id="last_name_kana" name="last_name_kana" value="<?php echo isset($_POST['last_name_kana']) ? esc_attr($_POST['last_name_kana']) : ''; ?>">
                            </div>
                            <div class="form-group half">
                                <label for="first_name_kana">名（フリガナ）</label>
                                <input type="text" id="first_name_kana" name="first_name_kana" value="<?php echo isset($_POST['first_name_kana']) ? esc_attr($_POST['first_name_kana']) : ''; ?>">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="email">メールアドレス<span class="required">*</span></label>
                            <input type="email" id="email" name="email" value="<?php echo isset($_POST['email']) ? esc_attr($_POST['email']) : esc_attr($user_email); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="phone">電話番号<span class="required">*</span></label>
                            <input type="tel" id="phone" name="phone" value="<?php echo isset($_POST['phone']) ? esc_attr($_POST['phone']) : esc_attr($user_phone); ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="postal_code">郵便番号<span class="required">*</span></label>
                            <div class="postal-code-wrapper">
                                <input type="text" id="postal_code" name="postal_code" placeholder="例: 1230001" value="<?php echo isset($_POST['postal_code']) ? esc_attr($_POST['postal_code']) : ''; ?>" required>
                                <button type="button" id="address-autofill" class="address-button">住所を自動入力</button>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="prefecture">都道府県<span class="required">*</span></label>
                            <select id="prefecture" name="prefecture" required>
                                <option value="">選択してください</option>
                                <?php
                                $prefectures = array(
                                    '北海道', '青森県', '岩手県', '宮城県', '秋田県', '山形県', '福島県',
                                    '茨城県', '栃木県', '群馬県', '埼玉県', '千葉県', '東京都', '神奈川県',
                                    '新潟県', '富山県', '石川県', '福井県', '山梨県', '長野県', '岐阜県',
                                    '静岡県', '愛知県', '三重県', '滋賀県', '京都府', '大阪府', '兵庫県',
                                    '奈良県', '和歌山県', '鳥取県', '島根県', '岡山県', '広島県', '山口県',
                                    '徳島県', '香川県', '愛媛県', '高知県', '福岡県', '佐賀県', '長崎県',
                                    '熊本県', '大分県', '宮崎県', '鹿児島県', '沖縄県'
                                );
                                
                                foreach ($prefectures as $pref) {
                                    $selected = isset($_POST['prefecture']) && $_POST['prefecture'] === $pref ? 'selected' : '';
                                    echo '<option value="' . esc_attr($pref) . '" ' . $selected . '>' . esc_html($pref) . '</option>';
                                }
                                ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="city">市区町村<span class="required">*</span></label>
                            <input type="text" id="city" name="city" value="<?php echo isset($_POST['city']) ? esc_attr($_POST['city']) : ''; ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="street_address">町名・番地<span class="required">*</span></label>
                            <input type="text" id="street_address" name="street_address" value="<?php echo isset($_POST['street_address']) ? esc_attr($_POST['street_address']) : ''; ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="building">建物名・部屋番号</label>
                            <input type="text" id="building" name="building" value="<?php echo isset($_POST['building']) ? esc_attr($_POST['building']) : ''; ?>">
                        </div>

                        <div class="form-group">
                            <label>生年月日<span class="required">*</span></label>
                            <div class="date-inputs">
                                <select id="birth_year" name="birth_year" required>
                                    <option value="">年</option>
                                    <?php
                                    $current_year = date('Y');
                                    $start_year = $current_year - 80;
                                    for ($year = $current_year - 18; $year >= $start_year; $year--) {
                                        $selected = isset($_POST['birth_year']) && intval($_POST['birth_year']) === $year ? 'selected' : '';
                                        echo '<option value="' . $year . '" ' . $selected . '>' . $year . '</option>';
                                    }
                                    ?>
                                </select>
                                <select id="birth_month" name="birth_month" required>
                                    <option value="">月</option>
                                    <?php
                                    for ($month = 1; $month <= 12; $month++) {
                                        $selected = isset($_POST['birth_month']) && intval($_POST['birth_month']) === $month ? 'selected' : '';
                                        echo '<option value="' . $month . '" ' . $selected . '>' . $month . '</option>';
                                    }
                                    ?>
                                </select>
                                <select id="birth_day" name="birth_day" required>
                                    <option value="">日</option>
                                    <?php
                                    for ($day = 1; $day <= 31; $day++) {
                                        $selected = isset($_POST['birth_day']) && intval($_POST['birth_day']) === $day ? 'selected' : '';
                                        echo '<option value="' . $day . '" ' . $selected . '>' . $day . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>性別<span class="required">*</span></label>
                            <div class="radio-group">
                                <label>
                                    <input type="radio" name="gender" value="男性" <?php echo isset($_POST['gender']) && $_POST['gender'] === '男性' ? 'checked' : ''; ?> required>
                                    男性
                                </label>
                                <label>
                                    <input type="radio" name="gender" value="女性" <?php echo isset($_POST['gender']) && $_POST['gender'] === '女性' ? 'checked' : ''; ?> required>
                                    女性
                                </label>
                                <label>
                                    <input type="radio" name="gender" value="その他" <?php echo isset($_POST['gender']) && $_POST['gender'] === 'その他' ? 'checked' : ''; ?> required>
                                    その他
                                </label>
                                <label>
                                    <input type="radio" name="gender" value="回答しない" <?php echo isset($_POST['gender']) && $_POST['gender'] === '回答しない' ? 'checked' : ''; ?> required>
                                    回答しない
                                </label>
                            </div>
                        </div>
                    </section>

                    <section class="form-section">
                        <h2 class="section-title">経歴情報</h2>

                        <div class="form-group">
                            <label for="employment_status">現在の就業状況</label>
                            <select id="employment_status" name="employment_status">
                                <option value="">選択してください</option>
                                <option value="在職中" <?php echo isset($_POST['employment_status']) && $_POST['employment_status'] === '在職中' ? 'selected' : ''; ?>>在職中</option>
                                <option value="離職中" <?php echo isset($_POST['employment_status']) && $_POST['employment_status'] === '離職中' ? 'selected' : ''; ?>>離職中</option>
                                <option value="学生" <?php echo isset($_POST['employment_status']) && $_POST['employment_status'] === '学生' ? 'selected' : ''; ?>>学生</option>
                                <option value="その他" <?php echo isset($_POST['employment_status']) && $_POST['employment_status'] === 'その他' ? 'selected' : ''; ?>>その他</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="education">最終学歴</label>
                            <select id="education" name="education">
                                <option value="">選択してください</option>
                                <option value="中学校卒" <?php echo isset($_POST['education']) && $_POST['education'] === '中学校卒' ? 'selected' : ''; ?>>中学校卒</option>
                                <option value="高校卒" <?php echo isset($_POST['education']) && $_POST['education'] === '高校卒' ? 'selected' : ''; ?>>高校卒</option>
                                <option value="専門学校卒" <?php echo isset($_POST['education']) && $_POST['education'] === '専門学校卒' ? 'selected' : ''; ?>>専門学校卒</option>
                                <option value="短大卒" <?php echo isset($_POST['education']) && $_POST['education'] === '短大卒' ? 'selected' : ''; ?>>短大卒</option>
                                <option value="大学卒" <?php echo isset($_POST['education']) && $_POST['education'] === '大学卒' ? 'selected' : ''; ?>>大学卒</option>
                                <option value="大学院卒" <?php echo isset($_POST['education']) && $_POST['education'] === '大学院卒' ? 'selected' : ''; ?>>大学院卒</option>
                                <option value="その他" <?php echo isset($_POST['education']) && $_POST['education'] === 'その他' ? 'selected' : ''; ?>>その他</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="qualification">保有資格・免許</label>
                            <textarea id="qualification" name="qualification" rows="4"><?php echo isset($_POST['qualification']) ? esc_textarea($_POST['qualification']) : ''; ?></textarea>
                            <div class="form-hint">取得年月とともに記入してください。</div>
                        </div>

                        <div class="form-group">
                            <label for="work_experience">職務経歴</label>
                            <textarea id="work_experience" name="work_experience" rows="6"><?php echo isset($_POST['work_experience']) ? esc_textarea($_POST['work_experience']) : ''; ?></textarea>
                            <div class="form-hint">これまでの職務経歴を記入してください。（例：〇〇年〇月～〇〇年〇月 株式会社〇〇 営業部）</div>
                        </div>

                        <div class="form-group">
                            <label for="resume_file">履歴書・職務経歴書（PDF）</label>
                            <input type="file" id="resume_file" name="resume_file" accept=".pdf">
                            <div class="form-hint">PDF形式のファイルをアップロードしてください。最大サイズ：5MB</div>
                        </div>
                    </section>

                    <section class="form-section">
                        <h2 class="section-title">応募情報</h2>

                        <div class="form-group">
                            <label for="motivation">志望動機<span class="required">*</span></label>
                            <textarea id="motivation" name="motivation" rows="6" required><?php echo isset($_POST['motivation']) ? esc_textarea($_POST['motivation']) : ''; ?></textarea>
                        </div>

                        <div class="form-group">
                            <label for="desired_salary">希望給与</label>
                            <input type="text" id="desired_salary" name="desired_salary" value="<?php echo isset($_POST['desired_salary']) ? esc_attr($_POST['desired_salary']) : ''; ?>">
                        </div>

                        <div class="form-group">
                            <label for="desired_working_hours">希望勤務時間・曜日</label>
                            <input type="text" id="desired_working_hours" name="desired_working_hours" value="<?php echo isset($_POST['desired_working_hours']) ? esc_attr($_POST['desired_working_hours']) : ''; ?>">
                        </div>
                    </section>

                    <div class="privacy-policy-section">
                        <h3>個人情報の取り扱いについて</h3>
                        <div class="privacy-policy-content">
                            <p>本応募フォームでご提供いただく個人情報は、採用選考および採用後の雇用手続きのために利用し、それ以外の目的では利用いたしません。</p>
                            <p>お預かりした個人情報は、適切に管理し、選考終了後は責任をもって廃棄いたします。</p>
                            <p>個人情報の取り扱いに関する詳細は、<a href="<?php echo home_url('/privacy-policy/'); ?>" target="_blank">プライバシーポリシー</a>をご確認ください。</p>
                        </div>
                        <div class="privacy-agreement">
                            <label>
                                <input type="checkbox" name="privacy_agreement" value="1" required>
                                個人情報の取り扱いに同意します<span class="required">*</span>
                            </label>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="submit" class="submit-button">応募する</button>
                        <a href="<?php echo get_permalink($job_id); ?>" class="cancel-button">キャンセル</a>
                    </div>
                </form>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // 郵便番号から住所自動入力
    $('#address-autofill').on('click', function() {
        var zipcode = $('#postal_code').val();
        
        if (!zipcode) {
            alert('郵便番号を入力してください');
            return;
        }
        
        // ハイフンを削除
        zipcode = zipcode.replace(/[０-９]/g, function(s) {
            return String.fromCharCode(s.charCodeAt(0) - 0xFEE0);
        }).replace(/[^\d]/g, '');
        
        // 郵便番号APIを使用して住所取得
        $.getJSON('https://zipcloud.ibsnet.co.jp/api/search?callback=?', {
            zipcode: zipcode
        }).done(function(data) {
            if (data.status === 200 && data.results) {
                var result = data.results[0];
                $('#prefecture').val(result.address1);
                $('#city').val(result.address2);
                $('#street_address').val(result.address3);
                $('#building').focus();
            } else {
                alert('該当する住所が見つかりませんでした');
            }
        }).fail(function() {
            alert('住所情報の取得に失敗しました');
        });
    });
});
</script>

<?php get_footer(); ?>