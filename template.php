<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bilingual Letter Template System</title>
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --accent-color: #e74c3c;
            --light-gray: #f5f7fa;
            --dark-gray: #34495e;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        body {
            background-color: var(--light-gray);
            color: var(--dark-gray);
            line-height: 1.6;
        }
        
        .container {
            max-width: 900px;
            margin: 0 auto;
            padding: 20px;
        }
        
        header {
            background-color: var(--primary-color);
            color: white;
            padding: 20px 0;
            text-align: center;
            border-radius: 8px 8px 0 0;
            margin-bottom: 20px;
            position: relative;
        }
        
        .language-toggle {
            position: absolute;
            top: 20px;
            right: 20px;
        }
        
        .language-btn {
            background-color: rgba(255, 255, 255, 0.2);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.3);
            padding: 8px 15px;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .language-btn:hover {
            background-color: rgba(255, 255, 255, 0.3);
        }
        
        h1 {
            font-size: 28px;
            margin-bottom: 5px;
        }
        
        .subtitle {
            font-size: 16px;
            opacity: 0.8;
        }
        
        .template-container {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            padding: 30px;
            margin-bottom: 20px;
        }
        
        .letter-header {
            display: flex;
            justify-content: space-between;
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 1px solid #eee;
        }
        
        .company-info {
            width: 50%;
        }
        
        .letter-details {
            width: 45%;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: var(--dark-gray);
        }
        
        input, select, textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        
        textarea {
            min-height: 150px;
            resize: vertical;
        }
        
        .letter-body {
            margin: 30px 0;
        }
        
        .letter-content {
            min-height: 400px;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 4px;
            background-color: #f9f9f9;
            line-height: 1.8;
        }
        
        .letter-footer {
            display: flex;
            justify-content: space-between;
            margin-top: 40px;
        }
        
        .sender-info {
            width: 48%;
        }
        
        .actions {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
        }
        
        button {
            padding: 12px 24px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-primary {
            background-color: var(--secondary-color);
            color: white;
        }
        
        .btn-primary:hover {
            background-color: #2980b9;
        }
        
        .btn-secondary {
            background-color: var(--dark-gray);
            color: white;
        }
        
        .btn-secondary:hover {
            background-color: #2c3e50;
        }
        
        .btn-danger {
            background-color: var(--accent-color);
            color: white;
        }
        
        .btn-danger:hover {
            background-color: #c0392b;
        }
        
        .formatting-toolbar {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
            padding: 10px;
            background-color: #f1f1f1;
            border-radius: 4px;
        }
        
        .format-btn {
            padding: 8px 12px;
            background-color: white;
            border: 1px solid #ddd;
            border-radius: 4px;
            cursor: pointer;
            font-weight: normal;
        }
        
        .format-btn:hover {
            background-color: #eee;
        }
        
        .letter-preview {
            background-color: white;
            padding: 30px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            margin-top: 30px;
            display: none;
        }
        
        .preview-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .preview-content {
            line-height: 1.8;
        }
        
        .signature-area {
            margin-top: 100px;
        }
        
        .signature-line {
            width: 200px;
            border-top: 1px solid #333;
            margin-top: 40px;
        }
        
        @media print {
            body * {
                visibility: hidden;
            }
            .letter-preview, .letter-preview * {
                visibility: visible;
            }
            .letter-preview {
                position: absolute;
                left: 0;
                top: 0;
                width: 100%;
                box-shadow: none;
            }
            .no-print {
                display: none;
            }
        }
        
        .lang-en {
            display: block;
        }
        
        .lang-am {
            display: none;
        }
        
        .amharic-font {
            font-family: 'Nyala', 'Arial', sans-serif;
        }
        
        .date-display {
            margin-top: 5px;
            font-size: 12px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container">
        <header>
            <div class="language-toggle">
                <button class="language-btn" id="toggleLanguage">Switch to Amharic</button>
            </div>
            <h1 class="lang-en">Internal Letter Template System</h1>
            <h1 class="lang-am">የውስጥ ደብዳቤ አብነት ስርዓት</h1>
            <p class="subtitle lang-en">Create professional letters for all departments</p>
            <p class="subtitle lang-am">ለሁሉም ክፍሎች ፕሮፌሽናል ደብዳቤዎችን ይፍጠሩ</p>
        </header>
        
        <div class="template-container">
            <div class="letter-header">
                <div class="company-info">
                    <div class="form-group">
                        <label for="companyName" class="lang-en">Company Name</label>
                        <label for="companyName" class="lang-am">የኩባንያ ስም</label>
                        <input type="text" id="companyName" value="Your Company Name">
                    </div>
                    <div class="form-group">
                        <label for="companyAddress" class="lang-en">Company Address</label>
                        <label for="companyAddress" class="lang-am">የኩባንያ አድራሻ</label>
                        <textarea id="companyAddress">123 Business Street, City, State 12345</textarea>
                    </div>
                </div>
                
                <div class="letter-details">
                    <div class="form-group">
                        <label for="date" class="lang-en">Date</label>
                        <label for="date" class="lang-am">ቀን</label>
                        <input type="date" id="date">
                        <div class="date-display lang-am" id="ethiopianDate"></div>
                    </div>
                    <div class="form-group">
                        <label for="department" class="lang-en">To Department</label>
                        <label for="department" class="lang-am">ወደ ክፍል</label>
                        <select id="department">
                            <option value="hr" data-en="Human Resources" data-am="ሰው ሃብት">Human Resources</option>
                            <option value="finance" data-en="Finance" data-am="ፋይናንስ">Finance</option>
                            <option value="it" data-en="IT Department" data-am="አይቲ ክፍል">IT Department</option>
                            <option value="marketing" data-en="Marketing" data-am="ግብይት">Marketing</option>
                            <option value="operations" data-en="Operations" data-am="ኦፕሬሽን">Operations</option>
                            <option value="sales" data-en="Sales" data-am="ሽያጭ">Sales</option>
                            <option value="other" data-en="Other" data-am="ሌላ">Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="subject" class="lang-en">Subject</label>
                        <label for="subject" class="lang-am">ርዕስ</label>
                        <input type="text" id="subject" placeholder="Enter letter subject">
                    </div>
                </div>
            </div>
            
            <div class="letter-body">
                <div class="formatting-toolbar">
                    <button class="format-btn" onclick="formatText('bold')"><strong>B</strong></button>
                    <button class="format-btn" onclick="formatText('italic')"><em>I</em></button>
                    <button class="format-btn" onclick="formatText('underline')"><u>U</u></button>
                    <button class="format-btn" onclick="formatText('insertUnorderedList')">• List</button>
                    <button class="format-btn" onclick="formatText('justifyLeft')">Left</button>
                    <button class="format-btn" onclick="formatText('justifyCenter')">Center</button>
                    <button class="format-btn" onclick="formatText('justifyRight')">Right</button>
                </div>
                
                <div class="letter-content" id="letterContent" contenteditable="true">
                    <p class="lang-en">Dear [Department Name],</p>
                    <p class="lang-am">ውድ [የክፍል ስም]፣</p>
                    <br>
                    <p class="lang-en">I am writing to you today regarding [topic of the letter]. This matter requires your immediate attention as it impacts our departmental operations.</p>
                    <p class="lang-am">ዛሬ ስለ [የደብዳቤ ርዕሰ ጉዳይ] ለመጻፍ እየመጣሁ ነው። ይህ ጉዳይ የኛን የክፍል ስራዎች ስለሚጎዳ ወዲያውኑ ትኩረትዎን ይጠይቃል።</p>
                    <br>
                    <p class="lang-en">Please review the information provided and take the necessary actions. Should you require any clarification or additional information, do not hesitate to contact me.</p>
                    <p class="lang-am">እባክዎ የቀረበውን መረጃ ይገምግሙ እና አስፈላጊውን እርምጃ ይውሰዱ። ማንኛውም ግልፅታ ወይም ተጨማሪ መረጃ ከፈለጉ ከእኔ ጋር ለመገናኘት አትዘግዩ።</p>
                    <br>
                    <p class="lang-en">We appreciate your cooperation in this matter and look forward to your response.</p>
                    <p class="lang-am">በዚህ ጉዳይ ላይ ያሳየችንን ትብብር እናመሰግናለን እና ለመልስዎ እንጠብቃለን።</p>
                </div>
            </div>
            
            <div class="letter-footer">
                <div class="sender-info">
                    <div class="form-group">
                        <label for="senderName" class="lang-en">Your Name</label>
                        <label for="senderName" class="lang-am">ስምዎ</label>
                        <input type="text" id="senderName" placeholder="Enter your name">
                    </div>
                    <div class="form-group">
                        <label for="senderPosition" class="lang-en">Your Position</label>
                        <label for="senderPosition" class="lang-am">ስራ መደብዎ</label>
                        <input type="text" id="senderPosition" placeholder="Enter your position">
                    </div>
                </div>
            </div>
            
            <div class="actions">
                <button class="btn-secondary" onclick="clearForm()">
                    <span class="lang-en">Clear Form</span>
                    <span class="lang-am">ቅጹን አጽዳ</span>
                </button>
                <button class="btn-primary" onclick="previewLetter()">
                    <span class="lang-en">Preview Letter</span>
                    <span class="lang-am">ደብዳቤውን አስቀድም</span>
                </button>
                <button class="btn-primary" onclick="printLetter()">
                    <span class="lang-en">Print Letter</span>
                    <span class="lang-am">ደብዳቤውን አትም</span>
                </button>
            </div>
        </div>
        
        <div class="letter-preview" id="letterPreview">
            <div class="preview-header">
                <h2 id="previewCompanyName">Your Company Name</h2>
                <p id="previewCompanyAddress">123 Business Street, City, State 12345</p>
            </div>
            
            <div class="preview-content">
                <p id="previewDate"></p>
                <p class="lang-en">To: <span id="previewDepartment">Human Resources</span> Department</p>
                <p class="lang-am">ወደ: <span id="previewDepartmentAm">ሰው ሃብት</span> ክፍል</p>
                <p class="lang-en">Subject: <span id="previewSubject"></span></p>
                <p class="lang-am">ርዕስ: <span id="previewSubjectAm"></span></p>
                <br>
                <div id="previewContent"></div>
                <div class="signature-area">
                    <p class="lang-en">Sincerely,</p>
                    <p class="lang-am">በጣም ከምንጋርበት፣</p>
                    <p id="previewSenderName"></p>
                    <p id="previewSenderPosition"></p>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Set today's date as default
        document.getElementById('date').valueAsDate = new Date();
        updateEthiopianDate();
        
        // Ethiopian calendar conversion function
        function toEthiopianDate(gregorianDate) {
            // Ethiopian calendar starts on September 11 (or 12 in leap years) of Gregorian calendar
            // This is a simplified conversion that should work for most dates
            const ethiopianMonths = [
                "መስከረም", "ጥቅምት", "ኅዳር", "ታህሳስ", "ጥር", "የካቲት", 
                "መጋቢት", "ሚያዝያ", "ግንቦት", "ሰኔ", "ሐምሌ", "ነሐሴ", "ጳጉሜ"
            ];
            
            const date = new Date(gregorianDate);
            const year = date.getFullYear();
            const month = date.getMonth();
            const day = date.getDate();
            
            // Calculate Ethiopian year (Ethiopian year starts in September)
            let ethYear = year - 8;
            if (month < 8 || (month === 8 && day < 11)) {
                ethYear = year - 9;
            }
            
            // Calculate day of the Ethiopian year
            const ethNewYear = new Date(year, 8, 11); // September 11
            if (month < 8 || (month === 8 && day < 11)) {
                ethNewYear.setFullYear(year - 1);
            }
            
            const diffTime = Math.abs(date - ethNewYear);
            const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
            
            // Calculate Ethiopian month and day
            let ethMonth = Math.floor(diffDays / 30);
            let ethDay = (diffDays % 30) + 1;
            
            // Handle Pagume (13th month) which has 5 or 6 days
            if (ethMonth === 12) {
                // Check if it's a leap year in the Ethiopian calendar
                // Ethiopian leap years occur every 4 years
                const isLeapYear = (ethYear + 1) % 4 === 0;
                if (ethDay > (isLeapYear ? 6 : 5)) {
                    ethMonth = 0;
                    ethDay = ethDay - (isLeapYear ? 6 : 5);
                    ethYear += 1;
                }
            }
            
            return {
                day: ethDay,
                month: ethiopianMonths[ethMonth],
                year: ethYear
            };
        }
        
        // Update Ethiopian date display
        function updateEthiopianDate() {
            const gregorianDate = document.getElementById('date').value;
            if (gregorianDate) {
                const ethDate = toEthiopianDate(gregorianDate);
                document.getElementById('ethiopianDate').textContent = 
                    `${ethDate.day} ${ethDate.month} ${ethDate.year}`;
            }
        }
        
        // Listen for date changes
        document.getElementById('date').addEventListener('change', updateEthiopianDate);
        
        // Language toggle functionality
        document.getElementById('toggleLanguage').addEventListener('click', function() {
            const isEnglish = document.querySelector('.lang-en').style.display !== 'none';
            const langEnElements = document.querySelectorAll('.lang-en');
            const langAmElements = document.querySelectorAll('.lang-am');
            
            if (isEnglish) {
                // Switch to Amharic
                langEnElements.forEach(el => el.style.display = 'none');
                langAmElements.forEach(el => el.style.display = 'block');
                document.getElementById('toggleLanguage').textContent = 'Switch to English';
                document.body.classList.add('amharic-font');
                
                // Update placeholders
                document.getElementById('subject').placeholder = 'የደብዳቤ ርዕስ አስገባ';
                document.getElementById('senderName').placeholder = 'ስምዎን አስገባ';
                document.getElementById('senderPosition').placeholder = 'ስራ መደብዎን አስገባ';
                
                // Update department options
                const departmentSelect = document.getElementById('department');
                for (let i = 0; i < departmentSelect.options.length; i++) {
                    departmentSelect.options[i].text = departmentSelect.options[i].getAttribute('data-am');
                }
                
                // Update Ethiopian date
                updateEthiopianDate();
            } else {
                // Switch to English
                langEnElements.forEach(el => el.style.display = 'block');
                langAmElements.forEach(el => el.style.display = 'none');
                document.getElementById('toggleLanguage').textContent = 'Switch to Amharic';
                document.body.classList.remove('amharic-font');
                
                // Update placeholders
                document.getElementById('subject').placeholder = 'Enter letter subject';
                document.getElementById('senderName').placeholder = 'Enter your name';
                document.getElementById('senderPosition').placeholder = 'Enter your position';
                
                // Update department options
                const departmentSelect = document.getElementById('department');
                for (let i = 0; i < departmentSelect.options.length; i++) {
                    departmentSelect.options[i].text = departmentSelect.options[i].getAttribute('data-en');
                }
            }
        });
        
        // Format text in the editable content area
        function formatText(command) {
            document.execCommand(command, false, null);
            document.getElementById('letterContent').focus();
        }
        
        // Clear the form
        function clearForm() {
            const isEnglish = document.querySelector('.lang-en').style.display !== 'none';
            const message = isEnglish ? 
                'Are you sure you want to clear the letter?' : 
                'ደብዳቤውን ማጽዳት ይፈልጋሉ?';
                
            if(confirm(message)) {
                document.getElementById('subject').value = '';
                document.getElementById('letterContent').innerHTML = `
                    <p class="lang-en">Dear [Department Name],</p>
                    <p class="lang-am">ውድ [የክፍል ስም]፣</p>
                    <br>
                    <p class="lang-en">I am writing to you today regarding [topic of the letter]. This matter requires your immediate attention as it impacts our departmental operations.</p>
                    <p class="lang-am">ዛሬ ስለ [የደብዳቤ ርዕሰ ጉዳይ] ለመጻፍ እየመጣሁ ነው። ይህ ጉዳይ የኛን የክፍል ስራዎች ስለሚጎዳ ወዲያውኑ ትኩረትዎን ይጠይቃል።</p>
                    <br>
                    <p class="lang-en">Please review the information provided and take the necessary actions. Should you require any clarification or additional information, do not hesitate to contact me.</p>
                    <p class="lang-am">እባክዎ የቀረበውን መረጃ ይገምግሙ እና አስፈላጊውን እርምጃ ይውሰዱ። ማንኛውም ግልፅታ ወይም ተጨማሪ መረጃ ከፈለጉ ከእኔ ጋር ለመገናኘት አትዘግዩ።</p>
                    <br>
                    <p class="lang-en">We appreciate your cooperation in this matter and look forward to your response.</p>
                    <p class="lang-am">በዚህ ጉዳይ ላይ ያሳየችንን ትብብር እናመሰግናለን እና ለመልስዎ እንጠብቃለን።</p>
                    <br>
                    <p class="lang-en">Sincerely,</p>
                    <p class="lang-am">በጣም ከምንጋርበት፣</p>
                    <p class="lang-en">[Your Name]</p>
                    <p class="lang-am">[ስምዎ]</p>
                    <p class="lang-en">[Your Position]</p>
                    <p class="lang-am">[ስራ መደብዎ]</p>
                `;
                document.getElementById('senderName').value = '';
                document.getElementById('senderPosition').value = '';
            }
        }
        
        // Preview the letter
        function previewLetter() {
            // Update preview content
            document.getElementById('previewCompanyName').textContent = document.getElementById('companyName').value;
            document.getElementById('previewCompanyAddress').textContent = document.getElementById('companyAddress').value;
            
            const date = new Date(document.getElementById('date').value);
            const isEnglish = document.querySelector('.lang-en').style.display !== 'none';
            
            if (isEnglish) {
                document.getElementById('previewDate').textContent = date.toLocaleDateString('en-US', { 
                    year: 'numeric', 
                    month: 'long', 
                    day: 'numeric' 
                });
            } else {
                const ethDate = toEthiopianDate(date);
                document.getElementById('previewDate').textContent = 
                    `${ethDate.day} ${ethDate.month} ${ethDate.year}`;
            }
            
            const departmentSelect = document.getElementById('department');
            const departmentTextEn = departmentSelect.options[departmentSelect.selectedIndex].getAttribute('data-en');
            const departmentTextAm = departmentSelect.options[departmentSelect.selectedIndex].getAttribute('data-am');
            
            document.getElementById('previewDepartment').textContent = departmentTextEn;
            document.getElementById('previewDepartmentAm').textContent = departmentTextAm;
            
            document.getElementById('previewSubject').textContent = document.getElementById('subject').value;
            document.getElementById('previewSubjectAm').textContent = document.getElementById('subject').value;
            
            document.getElementById('previewContent').innerHTML = document.getElementById('letterContent').innerHTML;
            document.getElementById('previewSenderName').textContent = document.getElementById('senderName').value || (isEnglish ? '[Your Name]' : '[ስምዎ]');
            document.getElementById('previewSenderPosition').textContent = document.getElementById('senderPosition').value || (isEnglish ? '[Your Position]' : '[ስራ መደብዎ]');
            
            // Show preview
            document.getElementById('letterPreview').style.display = 'block';
            
            // Scroll to preview
            document.getElementById('letterPreview').scrollIntoView({ behavior: 'smooth' });
        }
        
        // Print the letter
        function printLetter() {
            // If preview isn't shown, generate it first
            if(document.getElementById('letterPreview').style.display !== 'block') {
                previewLetter();
            }
            
            // Wait a moment for DOM to update then print
            setTimeout(() => {
                window.print();
            }, 500);
        }
        
        // Update department in salutation when department changes
        document.getElementById('department').addEventListener('change', function() {
            const departmentSelect = document.getElementById('department');
            const departmentTextEn = departmentSelect.options[departmentSelect.selectedIndex].getAttribute('data-en');
            const departmentTextAm = departmentSelect.options[departmentSelect.selectedIndex].getAttribute('data-am');
            
            const content = document.getElementById('letterContent');
            const firstParagraphEn = content.querySelector('.lang-en');
            const firstParagraphAm = content.querySelector('.lang-am');
            
            if(firstParagraphEn && firstParagraphEn.textContent.startsWith('Dear')) {
                firstParagraphEn.innerHTML = `Dear ${departmentTextEn},`;
            }
            
            if(firstParagraphAm && firstParagraphAm.textContent.startsWith('ውድ')) {
                firstParagraphAm.innerHTML = `ውድ ${departmentTextAm}፣`;
            }
        });
    </script>
</body>
</html>