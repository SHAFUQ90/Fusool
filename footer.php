<style>
        .glass-footer {
            /* الخلفية الزجاجية */
            background: linear-gradient(to top, rgba(0, 0, 0, 0.8), rgba(20, 20, 20, 0.4));
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            
            /* الحدود والتنسيق */
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            color: #ecf0f1;
            padding: 40px 20px 20px;
            margin-top: 80px; /* بدلاً من div فارغ */
            font-family: 'Tajawal', sans-serif;
            
            /* لضمان بقاء الفوتر في الأسفل في حال كان المحتوى قصيراً */
            width: 100%;
            box-sizing: border-box;
        }

        .footer-content {
            max-width: 1000px;
            margin: 0 auto;
            display: grid;
            /* تصميم متجاوب: أعمدة تتكيف مع حجم الشاشة */
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 40px;
            text-align: right;
        }

        .footer-section h3 {
            color: #f39c12;
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 20px;
            border-bottom: 2px solid rgba(243, 156, 18, 0.3);
            display: inline-block;
            padding-bottom: 5px;
        }

        .footer-section p {
            line-height: 1.8;
            color: #bdc3c7;
            font-size: 14px;
        }

        .footer-links {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .footer-links li {
            margin-bottom: 10px;
        }

        .footer-links a {
            text-decoration: none;
            color: #ecf0f1;
            transition: all 0.3s ease;
            font-size: 14px;
            display: inline-block;
        }

        .footer-links a:hover {
            color: #f39c12;
            transform: translateX(-5px); /* حركة بسيطة لليسار عند التحويم */
        }

        .footer-bottom {
            text-align: center;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid rgba(255, 255, 255, 0.05);
            font-size: 13px;
            color: #7f8c8d;
        }
    </style>

    <footer class="glass-footer">
        <div class="footer-content">
            
            <div class="footer-section">
                <h3>عن فصول</h3>
                <p>
                    منصة معرفية ثقافية تهدف لإحياء التراث العربي وقراءته برؤية معاصرة. نسعى لتقديم محتوى رصين يحترم عقل القارئ بعيداً عن السطحية.
                </p>
            </div>

            <div class="footer-section">
                <h3>روابط هامة</h3>
                <ul class="footer-links">
                    <li><a href="index.php">الواجهة الرئيسية</a></li>
                    <li><a href="about.php">من نحن</a></li>
                    <li><a href="privacy.php">سياسة الخصوصية</a></li> <li><a href="contact.php">اتصل بنا</a></li>
                </ul>
            </div>

            <div class="footer-section">
                <h3>تواصل معنا</h3>
                <ul class="footer-links">
                    <li>📧 info@fusool.com</li>
                    <li>📍 القاهرة، مصر</li>
                </ul>
            </div>

        </div>

        <div class="footer-bottom">
            <p>جميع الحقوق محفوظة &copy; <?php echo date("Y"); ?> لموقع فصول | تصميم وتطوير: يوسف البوتلي</p>
        </div>
    </footer>

</body>
</html>