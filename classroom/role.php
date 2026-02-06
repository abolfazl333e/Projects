<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>انتخاب نقش</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            //font-family: Vazirmatn, sans-serif;
            font-family: "B Nazanin" !important;
            background: linear-gradient(to bottom, #a8edea, #fed6e3);
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            overflow-x: hidden; /* جلوگیری از اسکرول افقی */
        }

        header {
            position: absolute;
            top: 25px;
            width: 100%;
            display: flex;
            justify-content: space-between;
            padding: 0 40px; /* فاصله از چپ و راست */
            box-sizing: border-box;
        }

        .header-btn {
            font-family: "B Nazanin" !important;
            background: #fff;
            border: none;
            border-radius: 8px;
            padding: 10px 25px;
            font-size: 15px;
            font-weight: bold;
            color: #333;
            cursor: pointer;
            box-shadow: 0 3px 10px rgba(0,0,0,0.2);
            transition: background 0.3s, transform 0.3s;
        }

        .header-btn:hover {
            background: #007bff;
            color: white;
            transform: scale(1.05);
        }

        h1 {
            margin-bottom: 40px;
            color: #333;
        }

        .role-container {
            display: flex;
            gap: 50px;
            flex-wrap: wrap;
            justify-content: center;
        }

        .role-card {
            background: #fff;
            border-radius: 15px;
            padding: 30px;
            width: 180px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
            transition: transform 0.3s, box-shadow 0.3s;
            cursor: pointer;
        }

        .role-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.3);
        }

        .role-card i {
            font-size: 60px;
            color: #007bff;
            margin-bottom: 15px;
        }

        .role-card span {
            display: block;
            font-size: 18px;
            font-weight: bold;
            color: #333;
        }
    </style>
</head>
<body>

    <header>
        <button class="header-btn" onclick="location.href='students_stars.php'">امتیازات</button>
        <button class="header-btn" onclick="location.href='top_student.php'">نفرات برتر هر ماه</button>
        <button class="header-btn" onclick="location.href='slider.php'"> گالری </button>
        <button class="header-btn" onclick="location.href='travel_gallery.php'"> سفرنامه </button>        
        <button class="header-btn" onclick="location.href='group_view.php'"> گروه ها </button>
        <button class="header-btn" onclick="location.href='experiments.php'"> آزمایشات </button>
                <button class="header-btn" onclick="location.href='view_exams.php'"> امتحانات </button>
        <button class="header-btn" onclick="location.href='attendance.php'">حضور و غیاب </button>
    </header>

    <h1>لطفاً نقش خود را انتخاب کنید</h1>

    <div class="role-container">
        <div class="role-card" onclick="goToLogin('student')">
            <i class="fas fa-user-graduate"></i>
            <span>دانش‌آموز</span>
        </div>
        <div class="role-card" onclick="goToLogin('teacher')">
            <i class="fas fa-chalkboard-teacher"></i>
            <span>معلم</span>
        </div>
    </div>

    <script>
        function goToLogin(role) {
            if(role === 'student') {
                window.location.href = 'student_login.php';
            } else if(role === 'teacher') {
                window.location.href = 'teacher_login.php';
            }
        }
    </script>

</body>
</html>
