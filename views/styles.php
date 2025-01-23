<style>
:root {
    /* System Colors */
    --wam-primary-100: #e6f0f5;
    --wam-primary-200: #bfd9e7;
    --wam-primary-300: #99c2d8;
    --wam-primary-400: #72abc9;
    --wam-primary-500: #2271b1;
    --wam-primary-600: #1d5d8c;
    --wam-primary-700: #164666;

    /* Semantic Colors */
    --wam-success-light: #dcf7e3;
    --wam-success: #00a32a;
    --wam-success-dark: #008a23;
    --wam-warning: #dba617;
    --wam-danger: #d63638;

    /* Neutral Colors */
    --wam-neutral-50: #f9fafb;
    --wam-neutral-100: #f3f4f6;
    --wam-neutral-200: #e5e7eb;
    --wam-neutral-300: #d1d5db;
    --wam-neutral-400: #9ca3af;
    --wam-neutral-500: #6b7280;
    --wam-neutral-600: #4b5563;
    --wam-neutral-700: #374151;

    /* Spacing System */
    --wam-space-xs: 0.25rem;  /* 4px */
    --wam-space-sm: 0.5rem;   /* 8px */
    --wam-space-md: 1rem;     /* 16px */
    --wam-space-lg: 1.5rem;   /* 24px */
    --wam-space-xl: 2rem;     /* 32px */
    --wam-space-2xl: 3rem;    /* 48px */

    /* Typography */
    --wam-font-sans: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Ubuntu, Arial, sans-serif;
    --wam-font-mono: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, monospace;
    
    /* Elevations */
    --wam-shadow-sm: 0 1px 2px rgba(0, 0, 0, 0.05);
    --wam-shadow-md: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
    --wam-shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
    
    /* Transitions */
    --wam-transition-fast: 150ms cubic-bezier(0.4, 0, 0.2, 1);
    --wam-transition-normal: 250ms cubic-bezier(0.4, 0, 0.2, 1);
}

/* Main Container */
.wrap {
    max-width: 1400px;
    margin: 0 auto;
    padding: var(--wam-space-lg);
}

/* Header */
.wp-heading-inline {
    font-family: var(--wam-font-sans);
    font-size: 1.875rem;
    color: var(--wam-neutral-700);
    margin-bottom: var(--wam-space-xl);
}

/* Stats Grid */
.wam-stats {
    margin: var(--wam-space-2xl) 0;
}

.wam-stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: var(--wam-space-lg);
}

/* Stats Box */
.wam-stat-box {
    background: var(--wam-neutral-50);
    padding: var(--wam-space-xl);
    border-radius: 12px;
    border: 1px solid var(--wam-neutral-200);
    box-shadow: var(--wam-shadow-sm);
    transition: var(--wam-transition-normal);
}

.wam-stat-box:hover {
    transform: translateY(-2px);
    box-shadow: var(--wam-shadow-md);
    border-color: var(--wam-primary-300);
}

.wam-stat-box h3 {
    color: var(--wam-neutral-600);
    font-size: 0.875rem;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.025em;
    margin-bottom: var(--wam-space-sm);
}

.wam-stat-box p {
    font-size: 2rem;
    font-weight: 600;
    color: var(--wam-primary-500);
    margin: 0;
    line-height: 1.2;
}

/* Table Container */
.wam-table-container {
    background: var(--wam-neutral-50);
    border-radius: 12px;
    box-shadow: var(--wam-shadow-sm);
    overflow: hidden;
    margin: var(--wam-space-2xl) 0;
}

/* Table Styles */
.wp-list-table {
    width: 100%;
    border-collapse: collapse;
    margin: 0;
}

.wp-list-table th {
    background: var(--wam-neutral-100) !important;
    padding: var(--wam-space-md) var(--wam-space-lg) !important;
    text-align: left;
    font-weight: 600;
    color: var(--wam-neutral-700);
    font-size: 0.875rem;
    text-transform: uppercase;
    letter-spacing: 0.025em;
}

.wp-list-table td {
    padding: var(--wam-space-md) var(--wam-space-lg) !important;
    border-bottom: 1px solid var(--wam-neutral-200);
    vertical-align: middle;
}

/* Status Badge */
.autoload-status {
    display: inline-flex;
    align-items: center;
    padding: var(--wam-space-xs) var(--wam-space-md);
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 500;
    text-transform: uppercase;
    letter-spacing: 0.05em;
}

.autoload-status.enabled {
    background: var(--wam-success-light);
    color: var(--wam-success);
}

.autoload-status.disabled {
    background: var(--wam-neutral-100);
    color: var(--wam-neutral-600);
}

/* Buttons */
.button {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: var(--wam-space-sm) var(--wam-space-lg) !important;
    border-radius: 6px !important;
    font-weight: 500;
    transition: var(--wam-transition-fast) !important;
    min-width: 120px;
}

.button-primary {
    background: var(--wam-primary-500) !important;
    border-color: var(--wam-primary-600) !important;
}

.button-primary:hover {
    background: var(--wam-primary-600) !important;
    transform: translateY(-1px);
}

.button-secondary {
    background: var(--wam-neutral-100) !important;
    border-color: var(--wam-neutral-300) !important;
    color: var(--wam-neutral-700) !important;
}

.button-secondary:hover {
    background: var(--wam-neutral-200) !important;
    transform: translateY(-1px);
}

/* Responsive Design */
@media screen and (max-width: 782px) {
    .wam-stats-grid {
        grid-template-columns: 1fr;
    }

    .wam-table-container {
        border-radius: 8px;
        margin: var(--wam-space-lg) 0;
    }

    .wp-list-table th,
    .wp-list-table td {
        padding: var(--wam-space-sm) !important;
    }

    .button {
        width: 100%;
        margin: var(--wam-space-xs) 0 !important;
    }
}

/* Animations */
@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

.wam-stats,
.wam-table-container {
    animation: fadeIn 0.3s ease-out forwards;
}

/* Accessibility */
@media (prefers-reduced-motion: reduce) {
    *,
    *::before,
    *::after {
        animation-duration: 0.01ms !important;
        animation-iteration-count: 1 !important;
        transition-duration: 0.01ms !important;
        scroll-behavior: auto !important;
    }
}

/* Print Styles */
@media print {
    .button,
    .wrap {
        break-inside: avoid;
    }
    
    .wam-stat-box {
        box-shadow: none !important;
        border: 1px solid #000;
    }
}
</style>
