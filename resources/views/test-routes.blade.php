<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>e-SPPD Routes & Buttons Test</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        .header {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            margin-bottom: 30px;
        }
        .header h1 {
            color: #333;
            margin-bottom: 10px;
        }
        .header p {
            color: #666;
        }
        .test-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .test-card {
            background: white;
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .test-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.3);
        }
        .test-card h3 {
            color: #333;
            margin-bottom: 15px;
            font-size: 18px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .test-card .routes {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }
        .route-btn {
            display: inline-block;
            padding: 10px 16px;
            background: #667eea;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-size: 14px;
            transition: background 0.3s;
            border: none;
            cursor: pointer;
            text-align: center;
        }
        .route-btn:hover {
            background: #764ba2;
        }
        .route-btn.success {
            background: #10b981;
        }
        .route-btn.success:hover {
            background: #059669;
        }
        .route-btn.warning {
            background: #f59e0b;
        }
        .route-btn.warning:hover {
            background: #d97706;
        }
        .route-btn.danger {
            background: #ef4444;
        }
        .route-btn.danger:hover {
            background: #dc2626;
        }
        .status {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }
        .status.ready {
            background: #d1fae5;
            color: #065f46;
        }
        .status.auth {
            background: #fef3c7;
            color: #92400e;
        }
        .breadcrumb {
            color: #666;
            font-size: 12px;
            margin-top: 8px;
        }
        .login-section {
            background: white;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            margin-bottom: 30px;
        }
        .login-section h2 {
            color: #333;
            margin-bottom: 20px;
        }
        .test-accounts {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }
        .account-box {
            background: #f3f4f6;
            padding: 15px;
            border-radius: 8px;
            border-left: 4px solid #667eea;
        }
        .account-box h4 {
            color: #333;
            margin-bottom: 8px;
        }
        .account-box p {
            color: #666;
            font-size: 13px;
            margin-bottom: 3px;
        }
        .account-box code {
            background: white;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: monospace;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>✓ e-SPPD Complete Implementation Test</h1>
            <p>All routes, controllers, and RBAC gates are configured and ready for testing</p>
        </div>

        <!-- Login Accounts Section -->
        <div class="login-section">
            <h2>Test Accounts (Akun Pengujian)</h2>
            <div class="test-accounts">
                <div class="account-box">
                    <h4>Admin</h4>
                    <p><strong>Email:</strong></p>
                    <p><code>admin@esppd.test</code></p>
                    <p><strong>Password:</strong></p>
                    <p><code>password123</code></p>
                </div>
                <div class="account-box">
                    <h4>Rektor</h4>
                    <p><strong>Email:</strong></p>
                    <p><code>rektor@esppd.test</code></p>
                    <p><strong>Password:</strong></p>
                    <p><code>password123</code></p>
                </div>
                <div class="account-box">
                    <h4>Warek</h4>
                    <p><strong>Email:</strong></p>
                    <p><code>warek@esppd.test</code></p>
                    <p><strong>Password:</strong></p>
                    <p><code>password123</code></p>
                </div>
                <div class="account-box">
                    <h4>Dekan</h4>
                    <p><strong>Email:</strong></p>
                    <p><code>dekan@esppd.test</code></p>
                    <p><strong>Password:</strong></p>
                    <p><code>password123</code></p>
                </div>
                <div class="account-box">
                    <h4>Wadek</h4>
                    <p><strong>Email:</strong></p>
                    <p><code>wadek@esppd.test</code></p>
                    <p><strong>Password:</strong></p>
                    <p><code>password123</code></p>
                </div>
                <div class="account-box">
                    <h4>Kaprodi</h4>
                    <p><strong>Email:</strong></p>
                    <p><code>kaprodi@esppd.test</code></p>
                    <p><strong>Password:</strong></p>
                    <p><code>password123</code></p>
                </div>
                <div class="account-box">
                    <h4>Dosen</h4>
                    <p><strong>Email:</strong></p>
                    <p><code>dosen@esppd.test</code></p>
                    <p><strong>Password:</strong></p>
                    <p><code>password123</code></p>
                </div>
            </div>
        </div>

        <!-- Routes Test Grid -->
        <div class="test-grid">
            <!-- SPD Management -->
            <div class="test-card">
                <h3>📄 SPD Management <span class="status ready">Ready</span></h3>
                <div class="routes">
                    <a href="{{ route('spd.index') }}" class="route-btn success">View All SPD</a>
                    <a href="{{ route('spd.create') }}" class="route-btn success">Create SPD</a>
                </div>
                <div class="breadcrumb">Routes: /spd, /spd/create</div>
            </div>

            <!-- Approvals -->
            <div class="test-card">
                <h3>✅ Approvals <span class="status ready">Ready</span></h3>
                <div class="routes">
                    <a href="{{ route('approvals.index') }}" class="route-btn success">Approval Queue</a>
                    <a href="{{ route('approvals.queue') }}" class="route-btn success">Pending Approvals</a>
                </div>
                <div class="breadcrumb">Routes: /approvals, /approvals/queue</div>
            </div>

            <!-- Reports -->
            <div class="test-card">
                <h3>📊 Reports <span class="status ready">Ready</span></h3>
                <div class="routes">
                    <a href="{{ route('reports.index') }}" class="route-btn success">All Reports</a>
                    <a href="{{ route('reports.builder') }}" class="route-btn success">Report Builder</a>
                </div>
                <div class="breadcrumb">Routes: /reports, /reports/builder</div>
            </div>

            <!-- Employee Management -->
            <div class="test-card">
                <h3>👥 Employees <span class="status auth">Admin Only</span></h3>
                <div class="routes">
                    <a href="{{ route('employees.index') }}" class="route-btn warning">Manage Employees</a>
                </div>
                <div class="breadcrumb">Routes: /employees</div>
            </div>

            <!-- Budget Management -->
            <div class="test-card">
                <h3>💰 Budgets <span class="status ready">Ready</span></h3>
                <div class="routes">
                    <a href="{{ route('budgets.index') }}" class="route-btn success">Budget Overview</a>
                </div>
                <div class="breadcrumb">Routes: /budgets</div>
            </div>

            <!-- Settings -->
            <div class="test-card">
                <h3>⚙️ Settings <span class="status ready">Ready</span></h3>
                <div class="routes">
                    <a href="{{ route('settings.index') }}" class="route-btn success">Settings</a>
                </div>
                <div class="breadcrumb">Routes: /settings</div>
            </div>

            <!-- Admin Users -->
            <div class="test-card">
                <h3>🔐 Admin Users <span class="status auth">Admin Only</span></h3>
                <div class="routes">
                    <a href="{{ route('admin.users.index') }}" class="route-btn danger">User Management</a>
                </div>
                <div class="breadcrumb">Routes: /admin/users</div>
            </div>

            <!-- Dashboard -->
            <div class="test-card">
                <h3>🏠 Dashboard <span class="status ready">Ready</span></h3>
                <div class="routes">
                    <a href="{{ route('dashboard') }}" class="route-btn success">Main Dashboard</a>
                </div>
                <div class="breadcrumb">Routes: /dashboard</div>
            </div>
        </div>

        <!-- RBAC Implementation Status -->
        <div class="header">
            <h2>✓ RBAC Implementation Status</h2>
            <ul style="margin: 15px 0; color: #666; line-height: 2;">
                <li>✓ 7 Roles: Admin, Rektor, Warek, Dekan, Wadek, Kaprodi, Employee</li>
                <li>✓ 17 Permissions: SPD, Approval, Finance, Reports, Admin categories</li>
                <li>✓ 16 Laravel Gates: create-spd, approve-spd, delegate-approval, etc</li>
                <li>✓ Approval Budget Limits: Wadek (10M), Dekan (50M), Warek (100M), Rektor (Unlimited)</li>
                <li>✓ Approval Delegation System: Time-bound with validity checks</li>
                <li>✓ 17 RBAC Tests: All Passing ✓</li>
                <li>✓ Sidebar: Updated with @can directives for permission-based access</li>
            </ul>
        </div>
    </div>
</body>
</html>
