import './bootstrap';

document.addEventListener('DOMContentLoaded', () => {
    const noticeSelector = '.system-message-toast, .system-message-modal, .alert';

    const dismissNotice = (notice) => {
        if (notice.matches('dialog') && notice.open) {
            notice.close();
            return;
        }

        notice.remove();
    };

    const initializeNotice = (notice) => {
        if (notice.dataset.autoDismissInitialized) {
            return;
        }

        notice.dataset.autoDismissInitialized = 'true';

        let dismissButton = notice.querySelector('[data-dismiss-notice]');
        if (!dismissButton) {
            dismissButton = document.createElement('button');
            dismissButton.type = 'button';
            dismissButton.className = 'system-alert-dismiss';
            dismissButton.setAttribute('data-dismiss-notice', '');
            dismissButton.setAttribute('aria-label', 'Dismiss notification');
            dismissButton.textContent = '×';
            notice.append(dismissButton);
        }

        const messageLength = notice.textContent.replace('×', '').trim().length;
        const isError = notice.classList.contains('is-error')
            || notice.classList.contains('alert-danger')
            || notice.getAttribute('role') === 'alert';
        let remaining = Math.min(8000, 3000 + messageLength * 50) + (isError ? 2000 : 0);
        let startedAt;
        let timer;

        const pause = () => {
            if (timer) {
                window.clearTimeout(timer);
                timer = undefined;
                remaining -= performance.now() - startedAt;
            }
        };

        const resume = () => {
            if (timer || notice.matches(':hover')) {
                return;
            }

            startedAt = performance.now();
            timer = window.setTimeout(() => dismissNotice(notice), Math.max(0, remaining));
        };

        notice.addEventListener('mouseenter', pause);
        notice.addEventListener('mouseleave', resume);
        dismissButton.addEventListener('click', () => dismissNotice(notice));
        resume();
    };

    const initializeNotices = (root = document) => {
        if (root.matches?.(noticeSelector)) {
            initializeNotice(root);
        }

        root.querySelectorAll?.(noticeSelector).forEach(initializeNotice);
    };

    initializeNotices();
    new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
            mutation.addedNodes.forEach((node) => {
                if (node instanceof Element) {
                    initializeNotices(node);
                }
            });
        });
    }).observe(document.body, { childList: true, subtree: true });

    document.querySelectorAll('[data-auto-open-dialog]').forEach((dialog) => {
        if (typeof dialog.showModal === 'function') {
            dialog.showModal();
        }
    });

    const scrollSections = [...document.querySelectorAll('[data-scroll-section]')];
    const scrollNavLinks = [...document.querySelectorAll('[data-scroll-nav]')];

    if (scrollSections.length && scrollNavLinks.length) {
        let scrollFramePending = false;
        const updateScrollNavigation = () => {
            scrollFramePending = false;
            const activeSection = [...scrollSections]
                .filter((section) => section.getBoundingClientRect().top <= window.innerHeight * 0.35)
                .at(-1) ?? scrollSections[0];

            scrollNavLinks.forEach((link) => {
                link.classList.toggle('active', link.dataset.scrollNav === activeSection.dataset.scrollSection);
            });
        };

        window.addEventListener('scroll', () => {
            if (!scrollFramePending) {
                scrollFramePending = true;
                window.requestAnimationFrame(updateScrollNavigation);
            }
        }, { passive: true });
        updateScrollNavigation();
    }

    const handleDropdownToggle = (toggle, dropdown) => {
        if (!dropdown) {
            return;
        }

        const isOpen = dropdown.classList.contains('open');

        document.querySelectorAll('.nav-dropdown.open, .notif-dropdown.open').forEach((openDropdown) => {
            if (openDropdown !== dropdown) {
                openDropdown.classList.remove('open');
                const openToggle = openDropdown.matches('.nav-dropdown')
                    ? openDropdown.querySelector('[data-dropdown-toggle]')
                    : openDropdown.previousElementSibling;
                if (openToggle) {
                    openToggle.setAttribute('aria-expanded', 'false');
                }
            }
        });

        dropdown.classList.toggle('open', !isOpen);
        toggle.setAttribute('aria-expanded', String(!isOpen));
    };

    document.querySelectorAll('[data-dropdown-toggle]').forEach((toggle) => {
        toggle.addEventListener('click', (event) => {
            event.preventDefault();
            event.stopPropagation();

            const dropdown = toggle.closest('.nav-dropdown');
            handleDropdownToggle(toggle, dropdown);
        });
    });

    document.querySelectorAll('[data-notification-toggle]').forEach((toggle) => {
        toggle.addEventListener('click', (event) => {
            event.preventDefault();
            event.stopPropagation();

            const dropdown = toggle.nextElementSibling;
            handleDropdownToggle(toggle, dropdown);
        });
    });

    document.addEventListener('click', (event) => {
        if (!event.target.closest('.nav-dropdown') && !event.target.closest('.notif-wrapper')) {
            document.querySelectorAll('.nav-dropdown.open, .notif-dropdown.open').forEach((dropdown) => {
                dropdown.classList.remove('open');
                const toggle = dropdown.matches('.nav-dropdown')
                    ? dropdown.querySelector('[data-dropdown-toggle]')
                    : dropdown.previousElementSibling;
                if (toggle) {
                    toggle.setAttribute('aria-expanded', 'false');
                }
            });
        }
    });

    document.querySelectorAll('[data-booking-filters]').forEach((filters) => {
        const root = filters.closest('[data-booking-filter-root]') ?? document;
        const searchInput = filters.querySelector('[data-booking-search]');
        const fromDateInput = filters.querySelector('[data-booking-from]');
        const toDateInput = filters.querySelector('[data-booking-to]');
        const sortButton = filters.querySelector('[data-booking-sort]');
        const panels = root.querySelectorAll('[data-booking-filter-panel]');

        const updateSortButton = () => {
            if (!sortButton) {
                return;
            }

            const newestFirst = sortButton.dataset.direction !== 'oldest';
            const icon = sortButton.querySelector('[data-booking-sort-icon]');
            const label = sortButton.querySelector('[data-booking-sort-label]');
            if (icon) {
                icon.className = `fa ${newestFirst ? 'fa-arrow-down' : 'fa-arrow-up'}`;
            }
            if (label) {
                label.textContent = newestFirst ? 'Newest first' : 'Oldest first';
            }
            sortButton.setAttribute(
                'aria-label',
                `Sorted ${newestFirst ? 'newest' : 'oldest'} first. Click to sort ${newestFirst ? 'oldest' : 'newest'} first.`,
            );
        };

        const applyFilters = () => {
            const searchTerm = searchInput?.value.trim().toLocaleLowerCase() ?? '';
            const fromDate = fromDateInput?.value ?? '';
            const toDate = toDateInput?.value ?? '';
            const newestFirst = sortButton?.dataset.direction !== 'oldest';

            panels.forEach((panel) => {
                const list = panel.querySelector('[data-booking-list]');
                const itemsContainer = panel.querySelector('[data-booking-items]');
                const items = [...panel.querySelectorAll('[data-booking-item]')];

                if (itemsContainer) {
                    items.sort((first, second) => {
                        const difference = Number(first.dataset.createdAt ?? 0) - Number(second.dataset.createdAt ?? 0);

                        return newestFirst ? -difference : difference;
                    });
                    items.forEach((item) => itemsContainer.append(item));
                }

                if (items.length === 0) {
                    return;
                }

                let visibleCount = 0;
                items.forEach((item) => {
                    const itemSearch = (item.dataset.search ?? item.textContent).toLocaleLowerCase();
                    const checkIn = item.dataset.checkIn ?? '';
                    const checkOut = item.dataset.checkOut ?? '';
                    const dateRangeValid = !fromDate || !toDate || fromDate <= toDate;
                    const dateMatches = dateRangeValid
                        && (!fromDate || checkOut > fromDate)
                        && (!toDate || checkIn <= toDate);
                    const matches = itemSearch.includes(searchTerm) && dateMatches;

                    item.hidden = !matches;
                    if (matches) {
                        visibleCount += 1;
                    }
                });

                if (list) {
                    list.hidden = visibleCount === 0;
                }
                const emptyState = panel.querySelector('[data-filter-empty]');
                if (emptyState) {
                    emptyState.hidden = visibleCount !== 0;
                }
            });
        };

        filters.addEventListener('submit', (event) => event.preventDefault());
        searchInput?.addEventListener('input', applyFilters);
        fromDateInput?.addEventListener('input', applyFilters);
        fromDateInput?.addEventListener('change', applyFilters);
        toDateInput?.addEventListener('input', applyFilters);
        toDateInput?.addEventListener('change', applyFilters);
        sortButton?.addEventListener('click', () => {
            sortButton.dataset.direction = sortButton.dataset.direction === 'oldest' ? 'newest' : 'oldest';
            updateSortButton();
            applyFilters();
        });
        filters.addEventListener('reset', () => {
            window.setTimeout(() => {
                if (sortButton) {
                    sortButton.dataset.direction = 'newest';
                    updateSortButton();
                }
                applyFilters();
            }, 0);
        });

        updateSortButton();
        applyFilters();
    });

    document.querySelectorAll('[data-history-archive-form]').forEach((form) => {
        const selectAll = form.querySelector('[data-archive-select-all]');
        const checkboxes = [...form.querySelectorAll('[data-archive-booking]')];
        const submitButton = form.querySelector('[data-archive-submit]');
        const selectionCount = form.querySelector('[data-archive-count]');

        const updateSelection = () => {
            const selectedCount = checkboxes.filter((checkbox) => checkbox.checked).length;
            if (submitButton) {
                submitButton.disabled = selectedCount === 0;
            }
            if (selectionCount) {
                selectionCount.textContent = `${selectedCount} selected`;
            }
            if (selectAll) {
                selectAll.checked = checkboxes.length > 0 && selectedCount === checkboxes.length;
                selectAll.indeterminate = selectedCount > 0 && selectedCount < checkboxes.length;
            }
        };

        selectAll?.addEventListener('change', () => {
            checkboxes.forEach((checkbox) => {
                checkbox.checked = selectAll.checked;
            });
            updateSelection();
        });
        checkboxes.forEach((checkbox) => checkbox.addEventListener('change', updateSelection));
        form.addEventListener('submit', (event) => {
            if (!checkboxes.some((checkbox) => checkbox.checked)) {
                event.preventDefault();
            }
        });
        updateSelection();
    });

    const statusTooltip = document.createElement('div');
    statusTooltip.id = 'booking-status-tooltip';
    statusTooltip.className = 'booking-status-tooltip';
    statusTooltip.setAttribute('role', 'tooltip');
    statusTooltip.hidden = true;
    document.body.append(statusTooltip);

    let activeStatusIndicator = null;
    let statusTooltipTimer;
    let statusTooltipHideTimer;

    const positionStatusTooltip = () => {
        if (!activeStatusIndicator) {
            return;
        }

        statusTooltip.textContent = activeStatusIndicator.dataset.statusTooltip;
        statusTooltip.hidden = false;
        const indicatorRect = activeStatusIndicator.getBoundingClientRect();
        const tooltipRect = statusTooltip.getBoundingClientRect();
        const viewportPadding = 12;
        const left = Math.max(
            viewportPadding,
            Math.min(
                indicatorRect.left + (indicatorRect.width - tooltipRect.width) / 2,
                window.innerWidth - tooltipRect.width - viewportPadding,
            ),
        );
        const above = indicatorRect.top - tooltipRect.height - 8;
        const top = above >= viewportPadding
            ? above
            : Math.min(indicatorRect.bottom + 8, window.innerHeight - tooltipRect.height - viewportPadding);

        statusTooltip.style.left = `${left}px`;
        statusTooltip.style.top = `${Math.max(viewportPadding, top)}px`;
        window.requestAnimationFrame(() => statusTooltip.classList.add('is-visible'));
    };

    const showStatusTooltip = (indicator) => {
        window.clearTimeout(statusTooltipTimer);
        window.clearTimeout(statusTooltipHideTimer);
        activeStatusIndicator = indicator;
        statusTooltipTimer = window.setTimeout(positionStatusTooltip, 500);
    };

    const hideStatusTooltip = (indicator) => {
        if (indicator !== activeStatusIndicator) {
            return;
        }

        window.clearTimeout(statusTooltipTimer);
        activeStatusIndicator = null;
        statusTooltip.classList.remove('is-visible');
        statusTooltipHideTimer = window.setTimeout(() => {
            if (!activeStatusIndicator) {
                statusTooltip.hidden = true;
            }
        }, 120);
    };

    document.querySelectorAll('[data-status-tooltip]').forEach((indicator) => {
        indicator.addEventListener('mouseenter', () => showStatusTooltip(indicator));
        indicator.addEventListener('mouseleave', () => hideStatusTooltip(indicator));
        indicator.addEventListener('focus', () => showStatusTooltip(indicator));
        indicator.addEventListener('blur', () => hideStatusTooltip(indicator));
    });

    document.querySelectorAll('[data-ajax-search]').forEach((form) => {
        const input = form.querySelector('input[name="q"]');
        const results = document.querySelector(form.dataset.results);
        const status = form.querySelector('[data-search-status]');
        const error = form.querySelector('[data-search-error]');
        let timer;
        let controller;

        if (!input || !results) {
            return;
        }

        const search = async () => {
            controller?.abort();
            const requestController = new AbortController();
            controller = requestController;
            form.setAttribute('aria-busy', 'true');
            if (error) {
                error.hidden = true;
                error.textContent = '';
            }
            if (status) {
                status.textContent = 'Searching…';
            }

            const url = new URL(form.action, window.location.href);
            new URLSearchParams(window.location.search).forEach((value, key) => {
                if (key !== input.name) {
                    url.searchParams.set(key, value);
                }
            });
            if (input.value.trim()) {
                url.searchParams.set(input.name, input.value.trim());
            } else {
                url.searchParams.delete(input.name);
            }

            try {
                const response = await fetch(url, {
                    headers: {
                        Accept: 'text/html',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    signal: requestController.signal,
                });
                if (!response.ok) {
                    throw new Error(`Search failed with status ${response.status}.`);
                }

                const responseDocument = new DOMParser().parseFromString(await response.text(), 'text/html');
                const replacement = responseDocument.querySelector(form.dataset.results);
                if (!replacement) {
                    throw new Error('Search response did not contain a results section.');
                }
                results.innerHTML = replacement.innerHTML;
                window.history.replaceState({}, '', url);
                if (status) {
                    status.textContent = 'Search results updated.';
                }
            } catch (searchError) {
                if (searchError.name === 'AbortError') {
                    return;
                }
                if (error) {
                    error.textContent = 'Search could not be completed. Please try again.';
                    error.hidden = false;
                }
                if (status) {
                    status.textContent = 'Search failed.';
                }
                console.error(searchError);
            } finally {
                if (controller === requestController) {
                    form.removeAttribute('aria-busy');
                }
            }
        };

        form.addEventListener('submit', (event) => event.preventDefault());
        input.addEventListener('input', () => {
            window.clearTimeout(timer);
            timer = window.setTimeout(search, 300);
        });
    });

    document.addEventListener('click', (event) => {
        const addPolicyButton = event.target.closest('[data-add-house-policy]');
        if (addPolicyButton) {
            const fieldGroup = addPolicyButton.closest('.house-policy-fields');
            const rows = fieldGroup.querySelector('[data-house-policies]');
            const template = fieldGroup.querySelector('[data-house-policy-template]');
            const existingRows = [...rows.querySelectorAll('[data-house-policy-row]')];
            const nextIndex = existingRows.reduce((highestIndex, row) => {
                const input = row.querySelector('[name^="policies["]');
                const index = Number(input?.name.match(/\[(\d+)\]/)?.[1] ?? -1);

                return Math.max(highestIndex, index);
            }, -1) + 1;
            const policyRow = template.content.cloneNode(true);

            policyRow.querySelectorAll('[name]').forEach((input) => {
                input.name = input.name.replace('__INDEX__', String(nextIndex));
            });
            rows.append(policyRow);
            rows.querySelector('[data-house-policy-row]:last-child input')?.focus();

            return;
        }

        const removePolicyButton = event.target.closest('[data-remove-house-policy]');
        if (removePolicyButton) {
            removePolicyButton.closest('[data-house-policy-row]')?.remove();

            return;
        }

        const addButton = event.target.closest('[data-add-social-link]');
        if (addButton) {
            const fieldGroup = addButton.closest('.form-group');
            const rows = fieldGroup.querySelector('[data-social-links]');
            const template = fieldGroup.querySelector('[data-social-link-template]');
            const existingRows = [...rows.querySelectorAll('[data-social-link-row]')];
            if (existingRows.length >= 10) {
                addButton.disabled = true;
                return;
            }

            const nextIndex = existingRows.reduce((highestIndex, row) => {
                const input = row.querySelector('[name^="social_links["]');
                const index = Number(input?.name.match(/\[(\d+)\]/)?.[1] ?? -1);

                return Math.max(highestIndex, index);
            }, -1) + 1;
            const row = template.content.cloneNode(true);

            row.querySelectorAll('[name]').forEach((input) => {
                input.name = input.name.replace('__INDEX__', String(nextIndex));
            });
            rows.append(row);
            addButton.disabled = existingRows.length + 1 >= 10;

            return;
        }

        const removeButton = event.target.closest('[data-remove-social-link]');
        if (removeButton) {
            const rows = removeButton.closest('[data-social-links]');
            const row = removeButton.closest('[data-social-link-row]');
            if (rows.querySelectorAll('[data-social-link-row]').length > 1) {
                row.remove();
            } else {
                row.querySelectorAll('input').forEach((input) => {
                    input.value = '';
                });
            }

            rows.parentElement.querySelector('[data-add-social-link]').disabled = false;
        }
    });

    document.addEventListener('click', (event) => {
        const button = event.target.closest('.tabs .tab-btn');
        if (button) {
            const tabs = button.closest('.tabs');
            const panes = tabs.parentElement.querySelectorAll('.tab-pane');
            tabs.querySelectorAll('.tab-btn').forEach((tab) => tab.classList.remove('active'));
            panes.forEach((pane) => pane.classList.remove('active'));
            button.classList.add('active');
            document.getElementById(button.dataset.tab)?.classList.add('active');
        }
    });
});
