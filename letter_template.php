<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Internal Letter Template System</title>
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
    </style>
</head>
<body>
    <div class="container">
        <header>
            <h1>Internal Letter Template System</h1>
            <p class="subtitle">Create professional letters for all departments</p>
        </header>
        
        <div class="template-container">
            <div class="letter-header">
                <div class="company-info">
                    <div class="form-group">
                        <label for="companyName">Company Name</label>
                        <input type="text" id="companyName" value="Your Company Name">
                    </div>
                    <div class="form-group">
                        <label for="companyAddress">Company Address</label>
                        <textarea id="companyAddress">123 Business Street, City, State 12345</textarea>
                    </div>
                </div>
                
                <div class="letter-details">
                    <div class="form-group">
                        <label for="date">Date</label>
                        <input type="date" id="date">
                    </div>
                    <div class="form-group">
                        <label for="department">To Department</label>
                        <select id="department">
                            <option value="hr">Human Resources</option>
                            <option value="finance">Finance</option>
                            <option value="it">IT Department</option>
                            <option value="marketing">Marketing</option>
                            <option value="operations">Operations</option>
                            <option value="sales">Sales</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="subject">Subject</label>
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
                    <p>Dear [Department Name],</p>
                    <br>
                    <p>I am writing to you today regarding [topic of the letter]. This matter requires your immediate attention as it impacts our departmental operations.</p>
                    <br>
                    <p>Please review the information provided and take the necessary actions. Should you require any clarification or additional information, do not hesitate to contact me.</p>
                    <br>
                    <p>We appreciate your cooperation in this matter and look forward to your response.</p>
                    
                </div>
            </div>
            
            <div class="letter-footer">
                <div class="sender-info">
                    <div class="form-group">
                        <label for="senderName">Your Name</label>
                        <input type="text" id="senderName" placeholder="Enter your name">
                    </div>
                    <div class="form-group">
                        <label for="senderPosition">Your Position</label>
                        <input type="text" id="senderPosition" placeholder="Enter your position">
                    </div>
                </div>
            </div>
            
            <div class="actions">
                <button class="btn-secondary" onclick="clearForm()">Clear Form</button>
                <button class="btn-primary" onclick="previewLetter()">Preview Letter</button>
                <button class="btn-primary" onclick="printLetter()">Print Letter</button>
            </div>
        </div>
        
        <div class="letter-preview" id="letterPreview">
            <div class="preview-header">
                <h2 id="previewCompanyName">Your Company Name</h2>
                <p id="previewCompanyAddress">123 Business Street, City, State 12345</p>
            </div>
            
            <div class="preview-content">
                <p id="previewDate"></p>
                <p>To: <span id="previewDepartment">Human Resources</span> Department</p>
                <p>Subject: <span id="previewSubject"></span></p>
                <br>
                <div id="previewContent"></div>
                <div class="signature-area">
                    <p>Sincerely,</p>
                    <p id="previewSenderName"></p>
                    <p id="previewSenderPosition"></p>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Set today's date as default
        document.getElementById('date').valueAsDate = new Date();
        
        // Format text in the editable content area
        function formatText(command) {
            document.execCommand(command, false, null);
            document.getElementById('letterContent').focus();
        }
        
        // Clear the form
        function clearForm() {
            if(confirm('Are you sure you want to clear the letter?')) {
                document.getElementById('subject').value = '';
                document.getElementById('letterContent').innerHTML = `
                    <p>Dear [Department Name],</p>
                    <br>
                    <p>I am writing to you today regarding [topic of the letter]. This matter requires your immediate attention as it impacts our departmental operations.</p>
                    <br>
                    <p>Please review the information provided and take the necessary actions. Should you require any clarification or additional information, do not hesitate to contact me.</p>
                    <br>
                    <p>We appreciate your cooperation in this matter and look forward to your response.</p>
                    <br>
                    <p>Sincerely,</p>
                    <p>[Your Name]</p>
                    <p>[Your Position]</p>
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
            document.getElementById('previewDate').textContent = date.toLocaleDateString('en-US', { 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric' 
            });
            
            const departmentSelect = document.getElementById('department');
            const departmentText = departmentSelect.options[departmentSelect.selectedIndex].text;
            document.getElementById('previewDepartment').textContent = departmentText;
            
            document.getElementById('previewSubject').textContent = document.getElementById('subject').value;
            document.getElementById('previewContent').innerHTML = document.getElementById('letterContent').innerHTML;
            document.getElementById('previewSenderName').textContent = document.getElementById('senderName').value || '[Your Name]';
            document.getElementById('previewSenderPosition').textContent = document.getElementById('senderPosition').value || '[Your Position]';
            
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
            const departmentText = departmentSelect.options[departmentSelect.selectedIndex].text;
            
            const content = document.getElementById('letterContent');
            const firstParagraph = content.querySelector('p');
            
            if(firstParagraph && firstParagraph.textContent.startsWith('Dear')) {
                firstParagraph.innerHTML = `Dear ${departmentText},`;
            }
        });
    </script>
</body>
</html>