<style>
    /* Sidebar Background & Layout */
    .fi-sidebar {
        background-color: #0F4C5C !important;
        border-right: none !important;
        box-shadow: none !important;
        display: flex !important;
        flex-direction: column !important;
        height: 100vh !important;
    }

    .fi-sidebar-nav {
        flex-grow: 1 !important;
        display: flex !important;
        flex-direction: column !important;
    }

    /* Sidebar Header (Brand) */
    .fi-sidebar-header {
        background-color: #0F4C5C !important;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1) !important;
    }

    .fi-sidebar-header .fi-logo {
        color: white !important;
    }

    /* Generic Sidebar Item (Inactive) */
    .fi-sidebar-item {
        margin-bottom: 4px !important;
    }

    .fi-sidebar-item-button {
        color: #E6F1F3 !important;
        background-color: transparent !important;
    }

    .fi-sidebar-item-icon {
        color: #E6F1F3 !important;
    }

    .fi-sidebar-item-label {
        color: #E6F1F3 !important;
        font-weight: 500 !important;
    }

    /* Hover State */
    .fi-sidebar-item-button:hover {
        background-color: rgba(255, 255, 255, 0.12) !important;
    }

    /* Active State (The Pill) */
    .fi-sidebar-item-active .fi-sidebar-item-button,
    .fi-sidebar-item.fi-active .fi-sidebar-item-button {
        background-color: #ffffff !important;
        color: #0F4C5C !important;
        border-radius: 9999px !important;
        /* Pill shape */
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06) !important;
    }

    .fi-sidebar-item-active .fi-sidebar-item-icon,
    .fi-sidebar-item.fi-active .fi-sidebar-item-icon {
        color: #0F4C5C !important;
    }

    .fi-sidebar-item-active .fi-sidebar-item-label,
    .fi-sidebar-item.fi-active .fi-sidebar-item-label {
        color: #0F4C5C !important;
    }

    /* Section Headers (dividers like "Surat Masuk") */
    /* Section Headers (dividers like "Surat Masuk") */
    .fi-sidebar-group-label {
        color: #E6F1F3 !important;
        font-size: 0.75rem !important;
        text-transform: uppercase !important;
        letter-spacing: 0.05em !important;
        margin-top: 1rem !important;
    }

    .fi-sidebar-group-label span {
        color: #E6F1F3 !important;
    }

    /* Logout Button Container (Sticky Bottom) */
    .custom-logout-area {
        margin-top: auto !important;
        padding: 1rem !important;
        border-top: 1px solid rgba(255, 255, 255, 0.1) !important;
        background-color: #0F4C5C !important;
        position: sticky !important;
        bottom: 0 !important;
        width: 100% !important;
    }

    /* Logout Button Styling */
    .custom-logout-btn {
        width: 100% !important;
        display: flex !important;
        align-items: center !important;
        padding: 0.5rem 0.75rem !important;
        color: white !important;
        border-radius: 0.5rem !important;
        transition: all 0.2s !important;
        cursor: pointer !important;
        font-weight: 500 !important;
        font-size: 0.875rem !important;
    }

    .custom-logout-btn:hover {
        background-color: rgba(239, 68, 68, 0.15) !important;
        /* Red-500 @ 15% */
    }

    .custom-logout-btn svg {
        margin-right: 0.75rem !important;
        width: 1.25rem !important;
        height: 1.25rem !important;
    }

    /* Main Dashboard Background */
    .fi-main {
        background-color: #F0F6F8 !important;
        /* Soft light teal/grey */
    }

    .antialiased.fi-body {
        background-color: #F0F6F8 !important;
    }

    /* Ensure Cards keep white background */
    .fi-section,
    .fi-wi-stats-overview-stat,
    .fi-ta-ctn {
        background-color: #ffffff !important;
        box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1), 0 1px 2px -1px rgba(0, 0, 0, 0.1) !important;
    }

    /* Topbar Background */
    .fi-topbar {
        background-color: #F0F6F8 !important;
    }

    /* Global Teal Overrides */
    /* Primary Buttons */
    .fi-btn-primary,
    .fi-btn.fi-color-primary,
    .fi-ac-btn-action.fi-color-primary {
        background-color: #0F4C5C !important;
        border-color: #0F4C5C !important;
        color: white !important;
    }

    .fi-btn-primary:hover,
    .fi-btn.fi-color-primary:hover,
    .fi-ac-btn-action.fi-color-primary:hover {
        background-color: #135d70 !important;
        /* Slightly lighter teal */
        border-color: #135d70 !important;
    }

    /* Outline Buttons */
    .fi-btn-outlined.fi-color-primary {
        background-color: transparent !important;
        color: #0F4C5C !important;
        border-color: #0F4C5C !important;
    }

    .fi-btn-outlined.fi-color-primary:hover {
        background-color: rgba(15, 76, 92, 0.1) !important;
    }

    /* Links */
    .fi-link.fi-color-primary,
    a.text-primary-600 {
        color: #0F4C5C !important;
    }

    .fi-link.fi-color-primary:hover,
    a.text-primary-600:hover {
        color: #135d70 !important;
    }

    /* Active Pagination */
    .fi-pagination-item-active span {
        background-color: #0F4C5C !important;
        border-color: #0F4C5C !important;
        color: white !important;
    }

    /* Tabs Styling */
    /* Inactive Tabs */
    .fi-tabs-item {
        background-color: transparent !important;
        color: #64748B !important; /* Slate-500 */
    }
    
    .fi-tabs-item:hover {
        background-color: rgba(241, 245, 249, 0.5) !important; /* Slate-100 @ 50% */
        color: #0F4C5C !important;
    }

    /* Active Tabs */
    .fi-tabs-item-active {
        background-color: #E2E8F0 !important; /* Slate-200 (Light Gray) */
        border-color: #CBD5E1 !important; /* Slate-300 */
        color: #0F172A !important; /* Slate-900 (Dark) */
    }
    
    /* Stats/Badges inside Tabs */
    .fi-tabs-item .fi-badge {
        background-color: #F1F5F9 !important; /* Slate-100 */
        color: #475569 !important; /* Slate-600 */
    }

    .fi-tabs-item-active .fi-badge {
        background-color: #FFFFFF !important;
        color: #0F4C5C !important; /* Teal text for contrast on badge */
        box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05) !important;
    }

    /* Badges */
    .fi-badge.fi-color-primary {
        background-color: rgba(15, 76, 92, 0.1) !important;
        color: #0F4C5C !important;
    }
</style>