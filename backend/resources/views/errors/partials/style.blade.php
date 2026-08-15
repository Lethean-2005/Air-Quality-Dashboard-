<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Nunito+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<style>
    * { box-sizing: border-box; }
    body {
        margin: 0;
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f9fafb;
        font-family: 'Nunito Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        padding: 24px;
    }
    .card {
        width: 100%;
        max-width: 420px;
        background: #ffffff;
        border: 1px solid #f3f4f6;
        border-radius: 24px;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.06);
        padding: 40px 32px;
        text-align: center;
    }
    .badge {
        width: 88px;
        height: 88px;
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 20px;
    }
    .badge img {
        height: 60px;
        width: auto;
        object-fit: contain;
    }
    .badge-good { background: #f0fdf4; }
    .badge-moderate { background: #fefce8; }
    .badge-poor { background: #fff7ed; }
    .badge-hazardous { background: #fef2f2; }
    .code {
        font-size: 13px;
        font-weight: 700;
        letter-spacing: 0.08em;
        color: #9ca3af;
        margin-bottom: 6px;
    }
    h1 {
        font-size: 20px;
        font-weight: 800;
        color: #111827;
        margin: 0 0 8px;
    }
    p {
        font-size: 14px;
        color: #6b7280;
        line-height: 1.5;
        margin: 0 0 24px;
    }
    .btn {
        display: inline-block;
        background: #111827;
        color: #ffffff;
        text-decoration: none;
        font-size: 14px;
        font-weight: 600;
        padding: 10px 20px;
        border-radius: 10px;
        transition: background-color 0.15s ease;
    }
    .btn:hover { background: #1f2937; }
</style>
