/**
 * Menu handler for sidebar navigation
 * Handles submenu toggling and automatic navigation to first child
 */

/**
 * Handle menu item clicks with submenu toggle and first child navigation
 * @param {HTMLElement} buttonElement - The button element that was clicked
 * @param {string} submenuId - The ID of the submenu to toggle
 * @param {string|null} firstChildRoute - The route of the first child item
 * @param {boolean} isCurrentlyExpanded - Whether the submenu is currently expanded
 * @param {boolean} isOnChildPage - Whether we're currently on one of the child pages
 */
window.handleMenuItemClick = function (buttonElement, submenuId, firstChildRoute, isCurrentlyExpanded, isOnChildPage) {
    const submenu = document.getElementById(submenuId);
    const arrowIcon = buttonElement.querySelector('.menu-item-arrow');
    
    if (!submenu || !arrowIcon) return;
    
    // Toggle submenu visibility
    const isHidden = submenu.classList.contains('hidden');
    
    if (isHidden) {
        // Expanding submenu
        submenu.classList.remove('hidden');
        arrowIcon.setAttribute('icon', 'lucide:chevron-up');
        
        // If there's a first child route, we're not currently on a child page, and we're not already on that route, navigate to it
        if (firstChildRoute && !isOnChildPage && window.location.href !== firstChildRoute) {
            // Small delay to allow the submenu to expand first
            setTimeout(() => {
                window.location.href = firstChildRoute;
            }, 100);
        }
    } else {
        // Collapsing submenu
        submenu.classList.add('hidden');
        arrowIcon.setAttribute('icon', 'lucide:chevron-right');
    }
};
