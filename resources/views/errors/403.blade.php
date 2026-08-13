<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>403 - Access Denied</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body class="d-flex align-items-center justify-content-center" style="min-height: 100vh; background: #f0f2f5;">
    <div class="text-center">
        <div style="font-size: 5rem; color: #ef4444;">
            <i class="bi bi-shield-x"></i>
        </div>
        <h1 style="font-weight: 700; color: #1a1d29;">403</h1>
        <h4 style="color: #6b7280;">Access Denied</h4>
        <p style="color: #9ca3af;">You don't have permission to access this page.</p>
        <a href="{{ url()->previous() }}" class="btn btn-primary mt-3">
            <i class="bi bi-arrow-left me-2"></i>Go Back
        </a>
    </div>
</body>
</html>