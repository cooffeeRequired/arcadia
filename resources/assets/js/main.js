console.log('Main JavaScript file loaded successfully.');

$(function() {
  const $mobileMenuButton = $('#mobile-menu-button');
  const $sidebar = $('#sidebar');
  const $sidebarOverlay = $('#sidebar-overlay');
  const $mainContent = $('#main-content');

  let isSidebarOpen = false;

  const openSidebar = () => {
    $sidebar.removeClass('-translate-x-full');
    $sidebarOverlay.removeClass('hidden');
    $mainContent.addClass('blur-sm');
    $mobileMenuButton.addClass('hidden'); // Skrýt tlačítko
    isSidebarOpen = true;
    
    // Přidat třídu pro lepší vizuální efekt
    $('body').addClass('sidebar-open');
  };

  const closeSidebar = () => {
    $sidebar.addClass('-translate-x-full');
    $sidebarOverlay.addClass('hidden');
    $mainContent.removeClass('blur-sm');
    $mobileMenuButton.removeClass('hidden'); // Zobrazit tlačítko
    isSidebarOpen = false;
    
    // Odebrat třídu
    $('body').removeClass('sidebar-open');
  };

  const toggleSidebar = () => {
    if (isSidebarOpen) {
      closeSidebar();
    } else {
      openSidebar();
    }
  };

  // Event listeners
  $mobileMenuButton.on('click', function(e) {
    e.preventDefault();
    e.stopPropagation();
    toggleSidebar();
  });

  $sidebarOverlay.on('click', function(e) {
    e.preventDefault();
    closeSidebar();
  });

  // Responsivní chování
  const handleResize = () => {
    const windowWidth = $(window).innerWidth();
    
    if (windowWidth >= 1024) {
      // Desktop - sidebar je vždy viditelný, žádné rozmazání
      $sidebar.removeClass('-translate-x-full');
      $sidebarOverlay.addClass('hidden');
      $mainContent.removeClass('blur-sm');
      $mobileMenuButton.addClass('hidden'); // Skrýt tlačítko na desktopu
      $('body').removeClass('sidebar-open');
      isSidebarOpen = false;
    } else {
      // Mobile - sidebar je skrytý, zobrazit tlačítko
      $sidebar.addClass('-translate-x-full');
      $sidebarOverlay.addClass('hidden');
      $mainContent.removeClass('blur-sm');
      $mobileMenuButton.removeClass('hidden'); // Zobrazit tlačítko na mobile
      $('body').removeClass('sidebar-open');
      isSidebarOpen = false;
    }
  };

  // Spustit při načtení stránky
  handleResize();

  // Spustit při změně velikosti okna
  $(window).on('resize', handleResize);

  // Zavřít sidebar při kliknutí na ESC
  $(document).on('keydown', function(e) {
    if (e.key === 'Escape' && isSidebarOpen && $(window).innerWidth() < 1024) {
      closeSidebar();
    }
  });

  // Zavřít sidebar při kliknutí mimo sidebar na mobile
  $(document).on('click', function(e) {
    if (isSidebarOpen && $(window).innerWidth() < 1024) {
      const $target = $(e.target);
      if (!$target.closest('#sidebar').length && !$target.closest('#mobile-menu-button').length) {
        closeSidebar();
      }
    }
  });
});

