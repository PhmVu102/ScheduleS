<style>
    .form-container { max-width: 600px; margin: 30px auto; background: #fff; padding: 30px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.05); font-family: sans-serif; }
    h2 { text-align: center; color: #333; margin-bottom: 25px; }
    .form-group { margin-bottom: 20px; }
    label { display: block; margin-bottom: 8px; font-weight: 600; color: #555; }
    input, select { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; box-sizing: border-box; }
    input:focus { border-color: #0066ff; outline: none; }
    .btn-submit { width: 100%; padding: 12px; background: #0066ff; color: white; border: none; border-radius: 6px; font-size: 16px; font-weight: bold; cursor: pointer; transition: 0.3s; }
    .btn-submit:hover { background: #0052cc; }
    .btn-back { display: block; text-align: center; margin-top: 15px; color: #666; text-decoration: none; }
    .alert-error { background: #fee2e2; color: #b91c1c; padding: 10px; border-radius: 6px; margin-bottom: 15px; text-align: center; }
</style>

<div class="form-container">
    <h2>Thêm Danh Mục Mới</h2>
    
    <?php if (!empty($msg)) echo $msg; ?>

    <form method="POST" action="">
        <div class="form-group">
            <label>Tên Danh Mục</label>
            <input type="text" name="name" id="name" onkeyup="ChangeToSlug();" placeholder="Ví dụ: Điện thoại iPhone" required>
        </div>

        <div class="form-group">
            <label>Slug (Đường dẫn)</label>
            <input type="text" name="slug" id="slug" placeholder="dien-thoai-iphone">
            <small style="color:#888;">Tự động tạo theo tên danh mục</small>
        </div>

        <div class="form-group">
            <label>Trạng Thái</label>
            <select name="status">
                <option value="1">Hiển thị</option>
                <option value="0">Ẩn</option>
            </select>
        </div>

        <button type="submit" class="btn-submit">Thêm Mới</button>
        <a href="?page=categorys" class="btn-back">Quay lại danh sách</a>
    </form>
</div>

<script>
    function ChangeToSlug() {
        var title, slug;
        title = document.getElementById("name").value;
        slug = title.toLowerCase();
        slug = slug.replace(/á|à|ả|ạ|ã|ă|ắ|ằ|ẳ|ẵ|ặ|â|ấ|ầ|ẩ|ẫ|ậ/gi, 'a');
        slug = slug.replace(/é|è|ẻ|ẽ|ẹ|ê|ế|ề|ể|ễ|ệ/gi, 'e');
        slug = slug.replace(/i|í|ì|ỉ|ĩ|ị/gi, 'i');
        slug = slug.replace(/ó|ò|ỏ|õ|ọ|ô|ố|ồ|ổ|ỗ|ộ|ơ|ớ|ờ|ở|ỡ|ợ/gi, 'o');
        slug = slug.replace(/ú|ù|ủ|ũ|ụ|ư|ứ|ừ|ử|ữ|ự/gi, 'u');
        slug = slug.replace(/ý|ỳ|ỷ|ỹ|ỵ/gi, 'y');
        slug = slug.replace(/đ/gi, 'd');
        slug = slug.replace(/\`|\~|\!|\@|\#|\||\$|\%|\^|\&|\*|\(|\)|\+|\=|\,|\.|\/|\?|\>|\<|\'|\"|\:|\;|_/gi, '');
        slug = slug.replace(/ /gi, "-");
        slug = slug.replace(/\-\-\-\-\-/gi, '-');
        slug = slug.replace(/\-\-\-\-/gi, '-');
        slug = slug.replace(/\-\-\-/gi, '-');
        slug = slug.replace(/\-\-/gi, '-');
        slug = '@' + slug + '@';
        slug = slug.replace(/\@\-|\-\@|\@/gi, '');
        document.getElementById('slug').value = slug;
    }
</script>