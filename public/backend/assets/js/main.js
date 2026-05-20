const initAppToggler = () => {

    const appTogglers = document.querySelectorAll(".app-toggler");
	const appMenubars = document.getElementById("appMenubar"); // single element

	if (!appMenubars) return;

	appTogglers.forEach(toggler => {
		toggler.addEventListener("click", () => {
			toggler.classList.toggle("active");

			if (window.innerWidth >= 1191) {
				// Toggle the value between 'full' and 'mini'
				const currentValue = document.documentElement.getAttribute("data-app-sidebar");
				document.documentElement.setAttribute(
					"data-app-sidebar",
					currentValue === "full" ? "mini" : "full"
				);
			} else {
				// For <1191, toggle 'open' on the single menubar
				appMenubars.classList.toggle("open");
			}
		});
	});

	// Hover logic for mini sidebar
	appMenubars.addEventListener("mouseenter", () => {
		if (document.documentElement.getAttribute("data-app-sidebar") === "mini") {
			document.documentElement.setAttribute("data-app-sidebar", "mini-hover");
		}
	});

	appMenubars.addEventListener("mouseleave", () => {
		if (document.documentElement.getAttribute("data-app-sidebar") === "mini-hover") {
			document.documentElement.setAttribute("data-app-sidebar", "mini");
		}
	});
};

const saerchList = () => {
	let listItems = [];

	// JSON load
	var searchJson = (typeof window.backendAssetPath !== 'undefined') ? (window.backendAssetPath + '/ajax/search.json') : '/backend/assets/ajax/search.json';
	$.getJSON(searchJson, function(data) {
		listItems = data.listItems;
	});

	// Search functionality
	$("#searchInput").on("keyup", function() {
		let query = $(this).val().toLowerCase();
		let searchContainer = $("#searchContainer");
		searchContainer.empty();
		searchContainer.hide();

		$('#recentlyResults').hide();

		if (query.length === 0) {
			searchContainer.hide();
			$('#recentlyResults').show();
			return;
		}

		let matched = listItems.filter(item =>
			item.name.toLowerCase().includes(query) ||
			item.url.toLowerCase().includes(query)
		);

		if (matched.length > 0) {
			let grouped = {};
			matched.forEach(item => {
				if (!grouped[item.category]) grouped[item.category] = [];
				grouped[item.category].push(item);
			});

			for (let cat in grouped) {
				searchContainer.append(
					`<span class="text-uppercase text-2xs fw-semibold text-muted d-block mb-2">${cat}</span>`
				);
				let ul = $("<ul class='list-inline search-list'></ul>");
				grouped[cat].forEach(item => {
					ul.append(
						`<li>
							<a class="search-item" href="${item.url}">
								<i class="${item.icon}"></i> <span>${item.name}</span>
							</a>
						</li>`
					);
				});
				searchContainer.append(ul);
			}
			searchContainer.show();
		} else {
			searchContainer.append(`
				<div class="text-center pb-5 pt-4">
					<div class="avatar avatar-lg bg-danger-subtle shadow-secondary rounded-circle text-danger mb-3 m-auto">
						<i class="fi fi-rr-assessment"></i>
					</div>
					<h5 class="mb-1">No result found</h5>
					<div class="text-muted">Please try again with a different query</div>
				</div>
			`);
			searchContainer.show();
		}
	});
};

const currentYear = () => {
    const elements = document.querySelectorAll('.currentYear');
    const currentYear = new Date().getFullYear();

    elements.forEach(element => {
        element.textContent = currentYear;
    });
};

const setElementHeight = () => {
    const footer = document.querySelector('.footer-wrapper');
	if (footer) {
		const footerHeight = footer ? footer.offsetHeight : 0;
		document.documentElement.style.setProperty('--footer-height', `${footerHeight}px`);
	}

	const chatBox = document.querySelector('.chat-wrapper');
	if (chatBox) {
		const chatHeight = chatBox.offsetHeight;
		document.documentElement.style.setProperty('--chat-height', `${chatHeight}px`);
	}

};

const initSelectPicker = () => {

	document.querySelectorAll('.select-status').forEach(dropdown => {
		const toggleButton = dropdown.querySelector('.dropdown-toggle');
		const items = dropdown.querySelectorAll('.dropdown-item');

		const updateButtonClassAndText = (text, selectedClass) => {
			// Remove btn-* except btn-sm, btn-lg
			toggleButton.classList.forEach(cls => {
				if (/^btn-/.test(cls) && !['btn-sm', 'btn-lg'].includes(cls)) {
					toggleButton.classList.remove(cls);
				}
			});

			if (selectedClass) {
				toggleButton.classList.add(...selectedClass.split(' '));
			}

			toggleButton.textContent = text;
		};

		// Handle default selection on page load
		const selectedItem = dropdown.querySelector('.dropdown-item[data-selected="true"]');
		if (selectedItem) {
			const defaultText = selectedItem.textContent.trim();
			const defaultClass = selectedItem.getAttribute('data-class');
			updateButtonClassAndText(defaultText, defaultClass);
		}

		// Handle selection on click
		items.forEach(item => {
			item.addEventListener('click', (e) => {
				e.preventDefault();
				items.forEach(i => i.removeAttribute('data-selected'));
				item.setAttribute('data-selected', 'true');

				const selectedText = item.textContent.trim();
				const selectedClass = item.getAttribute('data-class');
				updateButtonClassAndText(selectedText, selectedClass);
			});
		});
	});
};

function initSectionCheckboxSync() {
    document.querySelectorAll('.data-row-checkbox').forEach(function(section) {
        const masterCheckbox = section.querySelector('[data-row-checkbox]');
        const checkboxes = section.querySelectorAll('[data-checkbox]');

        if (!masterCheckbox || checkboxes.length === 0) return;

        masterCheckbox.addEventListener('change', function() {
            const checked = this.checked;
            checkboxes.forEach(function(cb) {
                cb.checked = checked;
            });
        });

        checkboxes.forEach(function(cb) {
            cb.addEventListener('change', function() {
                const allChecked = Array.from(checkboxes).every(c => c.checked);
                masterCheckbox.checked = allChecked;
            });
        });
    });
}

function initTooltips() {
	const tooltipTriggerList = document.querySelectorAll('[data-bs-toggle="tooltip"]');
	const tooltipList = [...tooltipTriggerList].map(tooltipTriggerEl => new bootstrap.Tooltip(tooltipTriggerEl));
}

function initPopover() {
	var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'))
	var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
		return new bootstrap.Popover(popoverTriggerEl)
	})
}

function initSidebarMenu() {

	// Only collapse menus that are NOT marked as route-active so route-driven expansion remains
	jQuery('.app-navbar .menubar > li.menu-arrow > a').next('.menu-inner')
		.not(function() { return jQuery(this).closest('.menu-item').hasClass('route-active'); })
		.slideUp();
	jQuery('.app-navbar .menu-inner > li > a').next('.menu-inner')
		.not(function() { return jQuery(this).closest('.menu-item').hasClass('route-active'); })
		.slideUp();

	jQuery('.app-navbar .menubar > li.menu-arrow > a, .app-navbar .menu-inner > li > a').unbind().on('click', function(e){
		if(jQuery(this).hasClass('open')){
			jQuery(this).removeClass('open');
			jQuery(this).parent('li').children('.menu-inner').slideUp();
		}else{
			if (!window.event.ctrlKey) {
				jQuery(this).addClass('open');
			}
			if(jQuery(this).parent('li').children('.menu-inner').length > 0){
				var href = jQuery(this).attr('href');
				if(!href || href === 'javascript:void(0);' || href === '#'){
					e.preventDefault();
				}
				jQuery(this).next('.menu-inner').slideDown();
				jQuery(this).parent('li').siblings('li').find('a:first').removeClass('open');
				jQuery(this).parent('li').siblings('li').children('.menu-inner').slideUp();
			}else{
				jQuery(this).next('.menu-inner').slideUp();
			}
		}
	});

	for (var nk = window.location,
		o = $(".app-navbar .menubar a").filter(function(){
		return this.href == nk;
	}).addClass("active").parent().addClass("active").parent().show().siblings('a').addClass("active open").parent().parent().show().siblings('a').addClass("open");;){
		if (!o.is("li")) {
			break;
		}
		o = o.parent().slideDown().parent('li').children('a').addClass("active");
	}
}
function initCheckable() {
    document.querySelectorAll('.checkable-wrapper').forEach(function(wrapper) {
        const checkAll = wrapper.querySelector('.checkable-check-all');
        const checkboxes = wrapper.querySelectorAll('.checkable-check-input');

        // Initialize checked state on load
        checkboxes.forEach(function(checkbox) {
            const item = checkbox.closest('.checkable-item');
            if (checkbox.checked && item) {
                item.classList.add('is-checked');
            }
        });

        // Handle "Select All"
        if (checkAll) {
            checkAll.addEventListener('change', function () {
                const isChecked = this.checked;
                checkboxes.forEach(function(checkbox) {
                    checkbox.checked = isChecked;
                    const item = checkbox.closest('.checkable-item');
                    if (item) {
                        item.classList.toggle('is-checked', isChecked);
                    }
                });
            });
        }

        // Handle individual checkbox toggle
        wrapper.addEventListener('change', function (e) {
            if (e.target.matches('.checkable-check-input')) {
                const item = e.target.closest('.checkable-item');
                if (item) {
                    item.classList.toggle('is-checked', e.target.checked);
                }

                // Update "Select All" state
                const allChecked = wrapper.querySelectorAll('.checkable-check-input:not(:checked)').length === 0;
                if (checkAll) {
                    checkAll.checked = allChecked;
                }
            }
        });
    });

	// Make sure route-active parents are shown and their anchors marked 'open' (so JS doesn't toggle them closed)
	jQuery('.app-navbar .menubar .menu-item.route-active').each(function(){
		var $li = jQuery(this);
		$li.children('a').addClass('open');
		$li.children('.menu-inner').show();
	});
}

function initEmailSidebarToggle() {
    const toggler = document.querySelector('.mail-sidebar-toggler');
    const sidebar = document.querySelector('.mail-sidebar');
    const overlay = document.querySelector('.sidebar-mobile-overlay');

    if (toggler && sidebar && overlay) {
        toggler.addEventListener('click', () => {
            sidebar.classList.toggle('open');
            overlay.classList.toggle('show', sidebar.classList.contains('open'));
        });

        overlay.addEventListener('click', () => {
            sidebar.classList.remove('open');
            overlay.classList.remove('show');
        });
    }
}

function initChatSidebarToggle() {
    const toggler = document.querySelector('.chat-sidebar-toggler');
    const sidebar = document.querySelector('.chat-sidebar');
    const overlay = document.querySelector('.sidebar-mobile-overlay');
    const btnClose = document.querySelector('.btn-close');

    if (toggler && sidebar && overlay) {
        toggler.addEventListener('click', () => {
            sidebar.classList.toggle('open');
            overlay.classList.toggle('show', sidebar.classList.contains('open'));
        });

        overlay.addEventListener('click', () => {
            sidebar.classList.remove('open');
            overlay.classList.remove('show');
        });

		btnClose.addEventListener('click', () => {
            sidebar.classList.remove('open');
            overlay.classList.remove('show');
        });
    }
}

function initBookmarks() {
    document.addEventListener('click', (e) => {
        const bookmark = e.target.closest('.mail-item-bookmark');
        if (bookmark) {
            bookmark.classList.toggle('active');
        }
    });
}

const initSelect2 = (context) => {
	if (typeof $ === 'undefined' || typeof $.fn.select2 === 'undefined') return;
	const $context = context ? $(context) : $(document);
	$context.find('.select2').addBack('.select2').each(function () {
		const $el = $(this);

		// Skip already-initialised instances
		if ($el.hasClass('select2-hidden-accessible')) return;

		const placeholder = $el.data('placeholder') || '';
		const ajaxUrl = $el.data('ajax-url') || $el.data('ajax') || null;
		const tags = $el.data('tags') || false;
		const options = {
			theme: 'bootstrap-5',
			width: '100%',
			placeholder: placeholder,
			allowClear: true,
		};

		// When inside a modal, anchor the dropdown to it so it renders above the overlay
		const $modal = $el.closest('.modal');
		if ($modal.length) {
			options.dropdownParent = $modal;
		}

		if (tags) options.tags = true;

		if (ajaxUrl) {
			options.ajax = {
				url: ajaxUrl,
				dataType: 'json',
				delay: 250,
				data: function (params) {
					return { q: params.term };
				},
				processResults: function (data) {
					if (!data) return { results: [] };
					if (Array.isArray(data)) {
						return { results: data.map(function (d) { return { id: d.id || d.value || d.key, text: d.text || d.name || d.label || d.title }; }) };
					}
					if (data.results) return data;
					var results = [];
					for (var i = 0; i < data.length; i++) {
						var d = data[i];
						results.push({ id: d.id || d.value, text: d.text || d.name || d.label || d.title });
					}
					return { results: results };
				},
				cache: true
			};
		}

		$el.select2(options);
	});
};

const ThemeSwitcher = () => {
	'use strict';

	// Cookie Utility: Get a cookie value by name
	const getCookie = (name) => {
		const match = document.cookie.match(new RegExp('(^| )' + name + '=([^;]+)'));
		return match ? match[2] : null;
	};

	// Cookie Utility: Set a cookie with optional expiry
	const setCookie = (name, value, days = 365) => {
		const expires = new Date(Date.now() + days * 864e5).toUTCString();
		document.cookie = `${name}=${value}; expires=${expires}; path=/`;
	};

	// Get stored theme from cookie (if available)
	const getStoredTheme = () => getCookie('theme');

	// Save selected theme to cookie
	const setStoredTheme = (theme) => setCookie('theme', theme);

	// Determine preferred theme: cookie > light (project does not support dark mode)
	const getPreferredTheme = () => {
		const storedTheme = getStoredTheme();
		if (storedTheme) return storedTheme;
		return 'light';
	};

	// Apply the theme to <html data-bs-theme="...">
	// "auto" is forced to light because this project does not use dark mode.
	const setTheme = (theme) => {
		const appliedTheme = (theme === 'auto') ? 'light' : theme;
		document.documentElement.setAttribute('data-bs-theme', appliedTheme);
	};

	// Update active state and icon based on selected theme
	const showActiveTheme = (theme, focus = false) => {
		const themeSwitcher = document.querySelector('#ld-theme');              // button
		const themeSwitcherText = document.querySelector('#ld-theme-text');    // button label
		const activeThemeIcon = document.querySelector('.theme-icon-active');  // <i> icon
		const btnToActive = document.querySelector(`[data-bs-theme-value="${theme}"]`);

		if (!themeSwitcher || !btnToActive || !activeThemeIcon) return;

		// Remove existing active styles
		document.querySelectorAll('[data-bs-theme-value]').forEach(el => {
			el.classList.remove('active');
			el.setAttribute('aria-pressed', 'false');
		});

		// Add active styles to the selected one
		btnToActive.classList.add('active');
		btnToActive.setAttribute('aria-pressed', 'true');

		// Replace icon class
		const selectedIcon = btnToActive.querySelector('i');
		if (selectedIcon) {
			activeThemeIcon.className = selectedIcon.className + ' theme-icon-active';
		}

		// Update button ARIA label
		const label = `${themeSwitcherText?.textContent || 'Theme'} (${btnToActive.dataset.bsThemeValue})`;
		themeSwitcher.setAttribute('aria-label', label);

		if (focus) themeSwitcher.focus();
	};

	// Project does not support dark mode — ignore system theme changes.

	// On page ready, apply theme and set up click events
	window.addEventListener('DOMContentLoaded', () => {
		const preferredTheme = getPreferredTheme();         // "light" / "dark" / "auto"
		setTheme(preferredTheme);                           // apply theme
		showActiveTheme(preferredTheme);                    // update icon/active

		// Theme buttons click handler
		document.querySelectorAll('[data-bs-theme-value]').forEach(toggle => {
			toggle.addEventListener('click', () => {
			const theme = toggle.getAttribute('data-bs-theme-value');
				setStoredTheme(theme);         // save in cookie
				setTheme(theme);              // apply
				showActiveTheme(theme, true); // update UI
			});
		});
	});
};

function initSidebarPanel() {
	document.addEventListener('click', function(e) {
		const toggler = e.target.closest('.sidebar-panel-toggler');
		if (toggler) {
			const panel = document.querySelector('.app-sidebar-panel');
			if (panel) {
				panel.classList.toggle('show');
			}
		}

		const closeBtn = e.target.closest('.sidebar-close');
		if (closeBtn) {
			document.querySelectorAll('.app-sidebar-panel').forEach(panel => {
				panel.classList.remove('show');
			});
		}
	});
}

document.addEventListener("DOMContentLoaded", () => {
    Waves.init();
	initAppToggler();
	saerchList();
	initSelect2();
	setElementHeight();
	currentYear();
	initSectionCheckboxSync();
	initSelectPicker();
	initTooltips();
	initPopover();
	initCheckable();
	initSidebarMenu();
	initEmailSidebarToggle();
	initChatSidebarToggle();
	initBookmarks();
	ThemeSwitcher();
	initSidebarPanel();
});


$(document).ready(function () {
	$('.dataTable').each(function() {
		const dtInstance = $(this).DataTable();
		dtInstance.on('draw.dt', function() {
			initSelectPicker();
			initSectionCheckboxSync();
			initSelect2();
		});
	});

	// Reinit select2 on modal shown — scope to the modal so dropdownParent is set correctly
	$(document).on('shown.bs.modal', '.modal', function () {
		initSelect2(this);
	});
});
