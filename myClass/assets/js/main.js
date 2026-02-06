
jQuery(function($) {

    const restBase = typeof myClass_object !== 'undefined' ? myClass_object.rest_url : '';
    const experimentsUploadUrl = myClass_object.uploads + '/experiments/';
    let allExperiments = []; 
    let removedImages = [];  
    const $modal = $('.students_login_modal');

    $(document).on("click", "#studentLoginBtn", function () {
        const studentModal = document.querySelector(".students_login_modal");

        studentModal.style.display = "flex";
    });
    $(document).on("click", "#closeModal", function () {
        const studentModal = document.querySelector(".students_login_modal");

        studentModal.style.display = "none";
    });

    const $form = $('#studentLoginForm');
    const $error = $('#loginError');
    const $close = $('#closeModal');

    if ($modal.length) {
       
        $close.on('click', function() {
            $modal.hide();
            $error.hide().text('');
            $form[0].reset();
        });

       
        $form.on('submit', function(e) {
            e.preventDefault(); 

            const username = $.trim($form.find('input[name="username"]').val());
            const password = $.trim($form.find('input[name="password"]').val());

            if (!username || !password) {
                $error.show().text('لطفاً همه فیلدها را پر کنید 🌟');
                return;
            }

            $error.hide().text('در حال بررسی...');

            $.ajax({
                url: myClass_object.rest_url + 'student-login',
                method: 'POST',
                data: {
                    username: username,
                    password: password
                },
                success: function(response) {
                    if (response.success) {
                        $error.hide();
                        $modal.hide();
                        $form[0].reset();
                        window.location.href = myClass_object.student_dashboard_url; 
                    } else {
                        $error.show().text(response.message || 'خطا در ورود');
                    }
                },
                error: function(xhr, status, err) {
                    console.error(err);
                    $error.show().text('خطا در ارتباط با سرور!');
                }
            });
        });
    }

    /* -------------------------------
       📘 بخش ۱: حضور و غیاب دانش‌آموزان
    -------------------------------- */
    function fetchStatus() {
        const $container = $('#studentsList');
        if ($container.length === 0 || typeof restBase === 'undefined') return; // اگر در صفحه حضور و غیاب نیستیم، خروج

        $.ajax({
            url: restBase + 'get_today_status',
            method: 'GET',
            dataType: 'json',
            success: function (data) {
                $container.empty(); // خالی کردن محتوای قبلی

                $.each(data, function (index, s) {
                    const statusClass =
                        s.status === 'حاضر' ? 'present' :
                            (s.status === 'مرخصی' ? 'leave' : 'absent');

                    const $div = $('<div>')
                        .addClass('student ' + statusClass)
                        .html('<div>' + s.name + '</div><div>' + s.status + (s.time ? ' — ' + s.time : '') + '</div>');

                    $container.append($div);
                });

                const $lastUpdated = $('#lastUpdated');
                if ($lastUpdated.length) {
                    $lastUpdated.text('آخرین بروزرسانی: ' + new Date().toLocaleTimeString());
                }
            },
            error: function (xhr, status, error) {
                console.error('خطا در دریافت وضعیت امروز:', error);
            }
        });
    }

    function finalizeAbsent() {
        if (typeof restBase === 'undefined') return;

        if (!confirm('آیا مطمئن هستید می‌خواهید بقیه دانش‌آموزان را غایب ثبت کنید؟')) return;

        $.ajax({
            url: restBase + 'finalize_absent',
            method: 'POST',
            dataType: 'json',
            success: function (json) {
                alert(json.msg || 'انجام شد');
                fetchStatus(); // فراخوانی مجدد برای به‌روزرسانی وضعیت
            },
            error: function (xhr, status, error) {
                console.error('خطا در نهایی‌سازی غیبت‌ها:', error);
                alert('خطایی رخ داد!');
            }
        });
    }

    // اگر عناصر صفحه حضور و غیاب موجود بودند، اجرا شود
    $(document).ready(function () {
        if ($('#studentsList').length) {
            $('#refreshBtn').on('click', fetchStatus);
            $('#finalizeBtn').on('click', finalizeAbsent);

            fetchStatus(); // بارگذاری اولیه
            setInterval(fetchStatus, 5000); // بروزرسانی هر ۵ ثانیه
        }
    });

    /* -------------------------------
       📗 بخش ۲: گالری تصاویر آزمایش‌ها
    -------------------------------- */
    $(document).ready(function () {
        const $modal = $("#imgModal");
        const $modalImg = $("#modalImage");
        const $captionText = $("#captionText");
        const $closeBtn = $(".close");
        const $nextBtn = $(".next-btn");
        const $prevBtn = $(".prev-btn");

        if ($modal.length && $modalImg.length && $captionText.length && $closeBtn.length && $nextBtn.length && $prevBtn.length) {
            let currentImages = [];
            let currentIndex = 0;
            let currentCaption = "";

            // باز کردن مدال
            $(".images").each(function () {
                const $container = $(this);
                const $imgs = $container.find("img");
                const title = $container.attr("data-title");

                $imgs.each(function (index) {
                    $(this).on("click", function () {
                        currentImages = $imgs.map(function () { return $(this).attr("src"); }).get();
                        currentCaption = title;
                        currentIndex = index;
                        openModal(currentImages[currentIndex], currentCaption);
                    });
                });
            });

            function openModal(src, caption) {
                $modal.css("display", "flex");
                $modalImg.attr("src", src);
                $captionText.text(caption);
            }

            function closeModal() {
                $modal.css("display", "none");
            }

            function showNext() {
                if (!currentImages.length) return;
                currentIndex = (currentIndex + 1) % currentImages.length;
                $modalImg.attr("src", currentImages[currentIndex]);
            }

            function showPrev() {
                if (!currentImages.length) return;
                currentIndex = (currentIndex - 1 + currentImages.length) % currentImages.length;
                $modalImg.attr("src", currentImages[currentIndex]);
            }

            $closeBtn.on("click", closeModal);
            $nextBtn.on("click", showNext);
            $prevBtn.on("click", showPrev);

            $modal.on("click", function (e) {
                if ($(e.target).is($modal)) closeModal();
            });

            $(document).on("keydown", function (e) {
                if ($modal.css("display") === "flex") {
                    if (e.key === "ArrowRight") showNext();
                    else if (e.key === "ArrowLeft") showPrev();
                    else if (e.key === "Escape") closeModal();
                }
            });
        }
    });

    // ==========================
    // 📌 توابع modal
    // ==========================

    function openModal() {
        $('#experimentModal').css('display', 'flex');
    }

    function closeModal() {
        $('#experimentModal').css('display', 'none');
        removedImages = [];
        $('#modalCurrentImages').empty();
        $('#modalExpImages').val('');
    }

    // ==========================
    // 📌 بارگذاری کارت‌ها
    // ==========================
    function loadExperiments() {
        const $container = $('#experimentsContainer');
        const $lessonSelect = $('#modalExpLesson');
        if ($container.length === 0 || $lessonSelect.length === 0) return;

        $.ajax({
            url: restBase + 'get_experiments',
            method: 'GET',
            dataType: 'json',
            success: function (data) {
                allExperiments = data.experiments;

                // پر کردن select درس‌ها
                $lessonSelect.empty().append('<option value="">انتخاب درس</option>');
                $.each(data.lessons, function (i, l) {
                    $lessonSelect.append('<option value="' + l.id + '">' + l.name + '</option>');
                });

                // پر کردن کارت‌ها
                $container.empty();
                $.each(data.experiments, function (i, exp) {
                    const lessonName = (data.lessons.find(l => l.id == exp.lesson_id)?.name) || '-';
                    const imagesHtml = exp.images.map(img =>
                        '<img src="' + experimentsUploadUrl + img + '" style="height:80px;border-radius:6px;">'
                    ).join('');

                    const $div = $('<div>')
                        .css({
                            background: '#fff',
                            borderRadius: '12px',
                            boxShadow: '0 4px 12px rgba(0,0,0,0.1)',
                            padding: '15px',
                            display: 'flex',
                            flexDirection: 'column'
                        })
                        .html(`
                        <h3>${exp.title}</h3>
                        <p>درس: ${lessonName}</p>
                        <div style="display:flex;gap:5px;overflow-x:auto;margin-bottom:10px;">${imagesHtml}</div>
                        <p>توضیح: ${exp.results}</p>
                        <div style="margin-top:auto;display:flex;justify-content:space-between;">
                            <button type="button" onclick="editExperiment(${exp.id})">ویرایش</button>
                            <button type="button" onclick="deleteExperiment(${exp.id})">حذف</button>
                        </div>
                    `);

                    $container.append($div);
                });
            },
            error: function (xhr, status, error) {
                console.error('خطا در دریافت آزمایش‌ها:', error);
            }
        });
    }

    // ==========================
    // 📌 ویرایش آزمایش
    // ==========================
    window.editExperiment = function (id) {
        const exp = allExperiments.find(e => e.id == id);
        if (!exp) return alert('آزمایش پیدا نشد');

        removedImages = [];

        const $expIdEl = $('#modalExpId');
        const $expTitleEl = $('#modalExpTitle');
        const $expLessonEl = $('#modalExpLesson');
        const $expResultsEl = $('#modalExpResults');
        const $modalTitle = $('#modalTitle');
        const $fileInput = $('#modalExpImages');
        const $imagesContainer = $('#modalCurrentImages');

        $expIdEl.val(exp.id || '');
        $expTitleEl.val(exp.title || '');
        $expResultsEl.val(exp.results || '');
        $modalTitle.text('ویرایش آزمایش');

        // ست کردن درس
        (function setLesson() {
            if ($expLessonEl.children().length === 0) {
                setTimeout(setLesson, 50);
            } else {
                $expLessonEl.val(String(exp.lesson_id));
            }
        })();

        // نمایش تصاویر قبلی
        $imagesContainer.empty();
        $.each(exp.images, function (i, filename) {
            const $imgDiv = $('<div>').css('position', 'relative');
            const $img = $('<img>')
                .attr('src', experimentsUploadUrl + filename)
                .css('width', '80px');
            const $btn = $('<button>')
                .text('×')
                .css({
                    position: 'absolute',
                    top: 0,
                    right: 0,
                    background: 'red',
                    color: 'white',
                    border: 'none',
                    borderRadius: '50%',
                    width: '20px',
                    height: '20px',
                    cursor: 'pointer'
                })
                .on('click', function () {
                    removedImages.push(filename);
                    $imgDiv.remove();
                });

            $imgDiv.append($img, $btn);
            $imagesContainer.append($imgDiv);
        });

        $fileInput.val('');
        openModal();
    };

    // --- ایجاد آزمایش جدید ---
    let $cancelBtn = $('#modalCancelBtn');
    if ($cancelBtn.length) {
        $cancelBtn.on('click', closeModal);
    }

    const $addBtn = $('#addExperimentBtn');
    if ($addBtn.length) {
        $addBtn.on('click', function () {
            $('#modalTitle').text('ایجاد آزمایش جدید');
            $('#modalExpId').val('');
            $('#modalExpTitle').val('');
            $('#modalExpLesson').val('');
            $('#modalExpResults').val('');
            $('#modalCurrentImages').html('');
            $('#modalExpImages').val('');
            removedImages = [];
            openModal();
        });
    }

    let $saveBtn = $('#modalSaveBtn');
    if ($saveBtn.length) {
        $saveBtn.on('click', saveExperiment);
    }

    // ==========================
    // 📌 ذخیره آزمایش
    // ==========================
    function saveExperiment() {
        const id = $('#modalExpId').val();
        const title = $('#modalExpTitle').val();
        const lesson_id = $('#modalExpLesson').val();
        const results = $('#modalExpResults').val();
        const activeClass = $('#class').val();

        console.log('ID IS :  ' + id);
        // بررسی وجود المنت فایل
        const $expImages = $('#expImages');
        const newFiles = $expImages.length ? $expImages[0].files : [];

        const formData = new FormData();
        formData.append('id', id);
        formData.append('title', title);
        formData.append('lesson_id', lesson_id);
        formData.append('results', results);

        $.each(removedImages, function(i, img) {
            formData.append('removed_images[]', img);
        });

        $.each(newFiles, function(i, file) {
            formData.append('images[]', file);
        });

        $.ajax({
            url: restBase + 'save_experiment',
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function(data) {
                if (data.ok) {
                    closeModal();
                    loadExperiments();
                }
            },
            error: function(xhr, status, error) {
                console.error('خطا در ذخیره آزمایش:', error);
                alert('خطایی رخ داد هنگام ذخیره آزمایش!');
            }
        });
    }

    // ==========================
    // 📌 حذف آزمایش
    // ==========================
    window.deleteExperiment = function (id) {
        if (!restBase) return;
        if (!confirm('آیا مطمئن هستید؟')) return;

        $.ajax({
            url: restBase + 'delete_experiment',
            type: 'POST',
            data: { id: id },
            dataType: 'json',
            success: function (data) {
                if (data.ok) {
                    alert('حذف شد.');
                    loadExperiments();
                } else {
                    alert('خطا: ' + (data.msg || 'نامشخص'));
                }
            },
            error: function (xhr, status, error) {
                console.error('خطا در حذف آزمایش:', error);
                alert('خطا در حذف رخ داد!');
            }
        });
    };

    // ==========================
    // 📌 اجرای اصلی
    // ==========================
    // --- دکمه لغو ---
    if ($cancelBtn.length) {
        $cancelBtn.on('click', closeModal);
    }

    // --- ذخیره تغییرات ---

    if ($saveBtn.length) {
        $saveBtn.on('click', function () {

            const id = $('#modalExpId').val();
            const title = $('#modalExpTitle').val();
            const lesson_id = $('#modalExpLesson').val();
            const results = $('#modalExpResults').val();
            const activeClass = $('#class').val();
            const newFiles = $('#modalExpImages')[0].files;

            const formData = new FormData();
            formData.append('id', id);
            formData.append('title', title);
            formData.append('lesson_id', lesson_id);
            formData.append('results', results);
            formData.append('activeClass', activeClass);

            $.each(removedImages, function (i, img) {
                formData.append('removed_images[]', img);
            });

            $.each(newFiles, function (i, file) {
                formData.append('images[]', file);
            });

            $.ajax({
                url: restBase + 'save_experiment',
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                dataType: 'json',
                success: function (data) {
                    if (data.ok) {
                        closeModal();
                        loadExperiments(); // رفرش کارت‌ها بعد از ذخیره
                    } else {
                        alert(data.message || 'خطا در ذخیره آزمایش');
                    }
                },
                error: function (xhr, status, error) {
                    console.error('خطا در ذخیره آزمایش:', error);
                    alert('خطایی رخ داد هنگام ذخیره آزمایش!');
                }
            });

        });
    }

    // --- بارگذاری اولیه ---
    if ($('#experimentsContainer').length) {
        loadExperiments();
    }

    // ==========================
    // 📌 دانش اموزان برتر هر ماه
    // ==========================
    function loadTopStudents() {
        $.ajax({
            url: restBase + 'top_students',
            type: 'GET',
            dataType: 'json',
            success: function (json) {
                const monthNames = ['', 'فروردین','اردیبهشت','خرداد','تیر','مرداد','شهریور','مهر','آبان','آذر','دی','بهمن','اسفند'];
                const $container = $('#accordionContainer');
                if ($container.length) $container.empty();
    
                $.each(json.data, function (i, month) {
                    let studentsHtml = '';
                    if (month.students.length) {
                        $.each(month.students, function (j, s) {
                            const stars = '⭐'.repeat(s.total_stars);
                            const img = myClass_object.uploads + 'students/' + s.id + '.jpg';
                            studentsHtml += `
                            <div class="top_students_card">
                                <div class="medal">🏆</div>
                                <img src="${img}" onerror="this.src='${myClass_object.uploads}students/default_student.png'">
                                <h3>${s.name}</h3>
                                <p>امتیاز: ${s.balance}</p>
                                <div class="stars">${stars}</div>
                            </div>`;
                        });
                    } else {
                        studentsHtml = '<p>دانش‌آموزی برای این ماه ثبت نشده است.</p>';
                    }
                    
                    let saveButtonHtml = '';
                    if (myClass_object.is_admin) {
                        saveButtonHtml = `
                            <div style="width:100%;text-align:center;margin-top:20px;">
                                <button class="save-top-btn" data-month="${month.month}" data-year="${json.year}">
                                    📋 ثبت در جدول
                                </button>
                            </div>`;
                    }
    
                    const $item = $(`
                        <div class="accordion-item">
                            <div class="accordion-header">
                                <span>${monthNames[month.month]} ${json.year}</span>
                                <span class="icon">▶</span>
                            </div>
                            <div class="accordion-content" style="max-height:0; overflow:hidden; transition:max-height 0.4s ease;">
                                ${studentsHtml}
                                ${saveButtonHtml}
                            </div>
                        </div>
                    `);
    
                    $container.append($item);
                });
    
                // فعال‌سازی آکاردئون با انیمیشن
                $container.find('.accordion-header').off('click').on('click', function () {
                    const $header = $(this);
                    const $content = $header.next('.accordion-content');
                    const $icon = $header.find('.icon');
    
                    if ($content.hasClass('active')) {
                        // بستن آیتم فعلی
                        $content.css('max-height', 0).removeClass('active');
                        $icon.removeClass('rotate');
                    } else {
                        // بستن هر آیتم باز دیگر
                        const $open = $container.find('.accordion-content.active');
                        if ($open.length) {
                            $open.css('max-height', 0).removeClass('active');
                            $open.prev('.accordion-header').find('.icon').removeClass('rotate');
                        }
    
                        // باز کردن آیتم فعلی
                        $content.css('max-height', $content[0].scrollHeight + 'px').addClass('active');
                        $icon.addClass('rotate');
                    }
                });
                
                $container.off('click', '.save-top-btn').on('click', '.save-top-btn', function () {
                    const month = $(this).data('month');
                    const year = $(this).data('year');
                    const $btn = $(this);
                
                    $btn.prop('disabled', true).text('در حال ثبت...');
                
                    $.ajax({
                        url: restBase + 'save_top_students',
                        type: 'POST',
                        data: JSON.stringify({ month: month, year: year }),
                        contentType: 'application/json',
                        success: function (res) {
                            alert(res.message || 'نفرات برتر ثبت شدند.');
                            $btn.text('ثبت شد ✅');
                        },
                        error: function (xhr) {
                            console.error(xhr.responseText);
                            alert('خطا در ثبت نفرات برتر!');
                            $btn.prop('disabled', false).text('📋 ثبت در جدول');
                        }
                    });
                });
            },
            error: function (xhr, status, error) {
                console.error('خطا در دریافت دانش‌آموزان برتر:', error);
            }
        });
    }
    
    // بارگذاری اولیه
    $(document).ready(function () {
        if ($('#accordionContainer').length) {
            loadTopStudents();
        }
    });


    /**********************************************************/
    const $container = $('#myClass-dashboard-container');
    if ($container.length) {
        // بارگذاری داشبورد با AJAX
        function loadDashboard(lesson_id = 0) {
            let url = myClass_object.rest_url + 'dashboard';
            if (lesson_id) url += '?lesson_id=' + lesson_id;

            $.getJSON(url)
                .done(function (data) {
                    if (!data.success) {
                        $container.html('<p style="color:red;">' + data.message + '</p>');
                        return;
                    }

                    const student = data.student;
                    const lessons = data.lessons;
                    const attendance = data.attendance;
                    const behavior = data.behavior;
                    const selected_lesson_id = data.selected_lesson_id;

                    // ساخت HTML داشبورد
                    let html = `
                    <div class="dashboard">
                        <div class="logout"><a href="#" id="logoutBtn"><i class="fas fa-door-open"></i> خروج</a></div>
                        <div class="emoji">🎓</div>
                        <h2>سلام ${student.name} 🌟</h2>
                        <div class="info">شغل: <strong>${student.job}</strong></div>
                        <div class="status">
                            حضور امروز: ${attendance.status=='حاضر'?'✅ حاضر':'❌ غایب'}
                            <br>⏰ ${attendance.time}
                        </div>

                        <form id="lessonForm">
                            <label><strong>📘 انتخاب درس:</strong></label><br>
                            <select name="lesson_id" id="lessonSelect">
                                <option value="">-- یکی از درس‌ها را انتخاب کن --</option>
                                ${lessons.map(lesson => `<option value="${lesson.id}" ${lesson.id==selected_lesson_id?'selected':''}>${lesson.name}</option>`).join('')}
                            </select>
                        </form>
                `;

                    if (behavior) {
                        html += `
                        <div class="dashboard_card">
                            <p>تعداد مثبت‌ها: <span class="positive">${behavior.positive_count}</span> 🌞</p>
                            <p>تعداد منفی‌ها: <span class="negative">${behavior.negative_count}</span> 🌧️</p>
                        </div>
                    `;
                    } else if (selected_lesson_id) {
                        html += `<div class="dashboard_card">برای این درس هنوز امتیازی ثبت نشده است 📘</div>`;
                    }

                    html += '</div>';
                    $container.html(html);
                })
                .fail(function () {
                    console.error('خطای سرور');
                    $container.html('<p style="color:red;">خطای سرور! دوباره تلاش کنید.</p>');
                });
        }

        // مدیریت کلیک‌ها (event delegation)
        $container.on('click', '#logoutBtn', function (e) {
            e.preventDefault();
            $.post(myClass_object.rest_url + 'logout', function (data) {
                if (data.success) window.location.href = data.redirect;
            }, 'json');
        });

        // مدیریت تغییر درس (event delegation)
        $container.on('change', '#lessonSelect', function () {
            loadDashboard($(this).val());
        });

        // بارگذاری اولیه داشبورد
        loadDashboard();
    }

    /*************************************************************************/

    // ==================== مدیریت دانش‌آموزان (myClass) ====================

    const $root = $('#myclass-students-root');

    const $csvInput  = $('#csvInput');
    const $uploadBtn = $('#uploadBtn');
    const $msgUpload = $('#msgUpload');
    const $msgEdit   = $('#msgEdit');
    const $tbody     = $('#studentsTable tbody');

    let csvFile = null;

    // ==================== رویدادها ====================


    $csvInput.on('change', function(e) {
        csvFile = e.target.files[0];
        $uploadBtn.prop('disabled', !csvFile);
    });

    $uploadBtn.on('click', async function() {
        if (!csvFile) return;

        const form = new FormData();
        form.append('csv_file', csvFile);

        $msgUpload.text('در حال آپلود و پردازش...');
        $uploadBtn.prop('disabled', true);

        try {
            const res = await fetch(myClass_object.rest_url + 'students/import', {
                method: 'POST',
                body: form
            });
            const data = await res.json();

            if (data.ok) {
                $msgUpload.text(`افزوده شد: ${data.added}`);
                if (typeof loadStudents === 'function') {
                    await loadStudents();
                }
            } else {
                $msgUpload.text(data.msg || 'خطا در پردازش فایل.');
            }
        } catch (err) {
            console.error(err);
            $msgUpload.text('خطا در ارتباط با سرور.');
        } finally {
            $uploadBtn.prop('disabled', false);
            $csvInput.val('');
            csvFile = null;
        }
    });

    // ==================== دریافت و نمایش دانش‌آموزان ====================

    function escapeHtml(str) {
        if (str === null || typeof str === 'undefined') return '';
        return String(str).replace(/[&<>"'`=\/]/g, function(s) {
            return {
                '&': '&amp;', '<': '&lt;', '>': '&gt;',
                '"': '&quot;', "'": '&#39;', '/': '&#x2F;',
                '`': '&#x60;', '=': '&#x3D;'
            }[s];
        });
    }

    // ==================== ساخت جدول ====================
    function buildTable(students) {
        $tbody.empty();

        students.forEach(function(s) {
            const $tr = $(`
                <tr data-id="${s.id}">
                    <td>${escapeHtml(s.id)}</td>
                    <td><input type="text" class="fld-name" value="${escapeHtml(s.name)}"></td>
                    <!-- <td><input type="text" class="fld-class" value="${escapeHtml(s.class)}" style="width: 40px !important;"></td> -->
                    <td><input type="text" class="fld-rfid" dir="ltr" style="font-family:monospace" value="${escapeHtml(s.rfid_tag)}"></td>
                    <td><input type="text" class="fld-job" value="${escapeHtml(s.job || '')}"></td>
                    <td><input type="text" class="fld-username" dir="ltr" value="${escapeHtml(s.username || '')}"></td>
                    <td>
                        <input type="password" class="fld-password" placeholder="${s.has_password ? 'رمز تنظیم شده — برای تغییر وارد کنید' : 'رمز ندارد'}">
                        <span class="showPwd">نمایش</span>
                    </td>
                    <td><input type="number" class="fld-must" min="0" max="1" value="${escapeHtml(s.must_change_password || 0)}"></td>
                    <td><button class="updateBtn">ثبت تغییرات</button></td>
                </tr>
            `);

            $tbody.append($tr);
        });

        attachEvents();
    }

    // ==================== مدیریت رویدادها ====================
    function attachEvents() {
        // دکمه ثبت تغییرات
        $tbody.find('.updateBtn').off('click').on('click', async function() {
            const $tr = $(this).closest('tr');
            const id = parseInt($tr.data('id') || 0, 10);
            const name = $tr.find('.fld-name').val().trim();
            // const cls = $tr.find('.fld-class').val().trim();
            const rfid = $tr.find('.fld-rfid').val().trim();
            const job = $tr.find('.fld-job').val().trim();
            const username = $tr.find('.fld-username').val().trim();
            const password = $tr.find('.fld-password').val();
            const must = parseInt($tr.find('.fld-must').val() || 0, 10);

            if (!name || !rfid || !username) {
                $msgEdit.text('نام، کارت RFID و نام کاربری الزامی‌اند.');
                return;
            }

            $msgEdit.text('در حال ثبت تغییرات...');

            try {
                const res = await fetch(myClass_object.rest_url + 'students/update', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ id, name, rfid_tag: rfid, job, username, password, must_change_password: must })
                });

                const data = await res.json();
                if (data.ok) {
                    $msgEdit.text('تغییرات با موفقیت ثبت شد.');
                    if (password) {
                        $tr.find('.fld-password').val('').attr('placeholder', 'رمز تنظیم شده');
                    }
                } else {
                    $msgEdit.text(data.msg || 'خطا در ثبت.');
                }
            } catch (e) {
                console.error(e);
                $msgEdit.text('خطا در ارتباط با سرور.');
            }
        });

        // نمایش/مخفی کردن رمز
        $tbody.find('.showPwd').off('click').on('click', function() {
            const $input = $(this).prev('input');
            if (!$input.length) return;

            if ($input.attr('type') === 'password') {
                $input.attr('type', 'text');
                $(this).text('مخفی');
            } else {
                $input.attr('type', 'password');
                $(this).text('نمایش');
            }
        });
    }

    // ==================== بارگذاری دانش‌آموزان ====================
    async function loadStudents() {
        $msgEdit.text('در حال بارگذاری لیست...');
        try {
            const res = await fetch(myClass_object.rest_url + 'students');
            const data = await res.json();

            if (data.ok && Array.isArray(data.students)) {
                buildTable(data.students);
                $msgEdit.text('');
            } else {
                $msgEdit.text('خطا در دریافت اطلاعات.');
            }
        } catch (err) {
            console.error(err);
            $msgEdit.text('خطا در ارتباط با سرور.');
        }
    }

    // ==================== دکمه‌های خروجی ====================
    $('#exportCsvBtn').off('click').on('click', function() {
        window.open(`${myClass_object.site_url}/wp-json/myClass/v1/students/export?type=csv`, '_blank');
    });

    $('#exportSqlBtn').off('click').on('click', function() {
        window.open(`${myClass_object.site_url}/wp-json/myClass/v1/students/export?type=sql`, '_blank');
    });

    // ==================== شروع ====================
    loadStudents();


     /********************************students-gallery-page****************************/

     if ($('.mySwiper').length) {
        var swiper = new Swiper(".mySwiper", {
            loop: true,
            navigation: {
                nextEl: ".swiper-button-next",
                prevEl: ".swiper-button-prev",
            },
            pagination: {
                el: ".swiper-pagination",
                clickable: true,
            },
            autoplay: {
                delay: 7000,
                disableOnInteraction: false,
            },
        });
    }
    
    
    /********************************manage-group-page****************************/

    
     $('#manage-group-close-edit').click(function(){
        $('#manage-group-edit-card').slideUp();
        const url = new URL(window.location.href);
        url.searchParams.delete('edit_group');
        window.history.replaceState({}, document.title, url.toString());
    });
    
    $('#manage-group-close-edit').on('click', function(){
        $('#manage-group-edit-card').hide(); // مخفی کردن فرم
    });

    // حذف چندتایی اعضای گروه با AJAX
    $('#manage-group-remove-selected').click(function(){
        if(!confirm('آیا مطمئن هستید که می‌خواهید این اعضا را حذف کنید؟')) return;

        var ids = [];
        $('#manage-group-member-list li').each(function(){
            var checkbox = $(this).find('.member-checkbox');
            if(checkbox.is(':checked')){
                ids.push($(this).data('sg-id'));
            }
        });

        if(ids.length === 0) return;

        $.ajax({
            url: ajaxurl, // وردپرس خودش ajaxurl را در ادمین تعریف می‌کند
            type: 'POST',
            data: {
                action: 'myclass_remove_group_members', // هک اختصاصی در functions.php برای AJAX
                sg_ids: ids
            },
            success: function(response){
                // حذف اعضای تیک‌خورده از لیست
                $('#manage-group-member-list li').each(function(){
                    var checkbox = $(this).find('.member-checkbox');
                    if(checkbox.is(':checked')) $(this).remove();
                });
            },
            error: function(){
                alert('خطا در حذف اعضا. دوباره تلاش کنید.');
            }
        });
    });


/********************************view-exams-page****************************/

    if($("#examDateFilter").length){
        $("#examDateFilter").persianDatepicker({
            format: 'YYYY-MM-DD',
            autoClose: true,
            observer: true,
            initialValue: false
        });
    }

    // آکاردئون
    $(".view-exam-accordion-header").on("click", function(){
        var body = $(this).next(".view-exam-accordion-body");
        $(".view-exam-accordion-body").not(body).slideUp();
        body.slideToggle();
    });

    // باز کردن مدال عکس امتحان
    $(".view-exam-image").on("click", function(){
        $("#view-exam-modal-img").attr("src", $(this).attr("src"));
        $("#view-exam-modal").css("display","flex").hide().fadeIn(200);
    });


    // بستن مدال با کلیک
    $("#view-exam-modal").on("click", function(){
        $(this).fadeOut();
    });

    // اعمال فیلتر
    $("#applyFilter").on("click", function(){
        var lesson = $("#lessonFilter").val();
        var date = $("#examDateFilter").val();
        var url = "?";
        if(lesson) url += "lesson_id=" + lesson + "&";
        if(date) url += "examDate=" + encodeURIComponent(date);
        window.location.href = url;
    });
    
    /********************************manage-exam-page****************************/


    const form = $("#view-exams-admin-examForm");
    if (form.length) {
        
        $(document).on("click", ".view-exams-admin-score-box", function(){
            const parent = $(this).closest(".view-exams-admin-card");
            parent.find(".view-exams-admin-score-box").removeClass("selected");
            $(this).addClass("selected");
        
            if(parent.find("input.score-input").length==0){
                parent.append('<input type="hidden" class="score-input" name="score_'+parent.data("student")+'" value="'+$(this).data("score")+'">');
            } else {
                parent.find("input.score-input").val($(this).data("score"));
            }
        });


        form.on("submit", function(e){
            e.preventDefault();

            const formData = new FormData(this);

            console.log("📦 محتوای فرم:");
            for (const [key, value] of formData.entries()) {
                console.log(key, value);
            }

            $.ajax({
                url: myClass_object.rest_url + "save_exam",
                type: "POST",
                data: formData,
                processData: false,
                contentType: false,
                dataType: "json",
                success: function(res){
                    console.log("✅ پاسخ:", res);
                    alert(res.message);
                    if(res.success) location.reload();
                },
                error: function(xhr){
                    console.error("❌ خطای AJAX:", xhr.responseText);
                }
            });
        });
    } else {
        console.warn("⚠️ فرم myclass-examForm در این صفحه وجود ندارد");
    }

});



