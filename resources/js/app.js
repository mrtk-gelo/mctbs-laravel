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

    document.addEventListener('click', (event) => {
        const openButton = event.target.closest('[data-open-arrival-review]');
        if (openButton) {
            const dialog = document.getElementById(`arrival-review-${openButton.dataset.openArrivalReview}`);
            if (dialog && typeof dialog.showModal === 'function') {
                dialog.showModal();
            }

            return;
        }

        if (event.target.closest('[data-close-arrival-review]')) {
            event.target.closest('dialog')?.close();
        }
    });

    document.querySelectorAll('[data-auto-open-dialog]').forEach((dialog) => {
        if (typeof dialog.showModal === 'function') {
            dialog.showModal();
        }
    });
    document.querySelectorAll('[data-auto-open-arrival-review="true"]').forEach((dialog) => {
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

document.addEventListener('DOMContentLoaded', () => {
    const filterToggle = document.querySelector('.analytics-filter-toggle');
    const filterPanel = document.getElementById('analytics-filter-panel');
    filterToggle?.addEventListener('click', () => {
        const opening = filterPanel.hidden;
        filterPanel.hidden = !opening;
        filterToggle.setAttribute('aria-expanded', String(opening));
    });

    const setReportTab = (tabName) => {
        document.querySelectorAll('[data-report-tab]').forEach((tab) => {
            const active = tab.dataset.reportTab === tabName;
            tab.classList.toggle('active', active);
            tab.setAttribute('aria-selected', String(active));
        });
        document.querySelectorAll('[data-report-panel]').forEach((panel) => {
            panel.classList.toggle('active', panel.dataset.reportPanel === tabName);
        });
        const hiddenTab = filterPanel?.querySelector('input[name="report_tab"]');
        if (hiddenTab) {
            hiddenTab.value = tabName;
        }
    };
    document.querySelectorAll('[data-report-tab]').forEach((tab) => {
        tab.addEventListener('click', () => setReportTab(tab.dataset.reportTab));
    });
    document.querySelectorAll('[data-switch-report-tab]').forEach((button) => {
        button.addEventListener('click', () => setReportTab(button.dataset.switchReportTab));
    });

    document.querySelectorAll('[data-breakdown-section]').forEach((tab) => {
        tab.addEventListener('click', () => {
            document.querySelectorAll('[data-breakdown-section]').forEach((item) => item.classList.toggle('active', item === tab));
            document.querySelectorAll('[data-breakdown-panel]').forEach((panel) => {
                panel.classList.toggle('active', panel.dataset.breakdownPanel === tab.dataset.breakdownSection);
            });
        });
    });

    const chartContainer = document.getElementById('analytics-line-chart');
    const chartTitle = document.getElementById('analytics-chart-title');
    const chartHeadline = document.getElementById('analytics-chart-headline');
    const formatCurrency = (value) => new Intl.NumberFormat('en-PH', {
        style: 'currency',
        currency: 'PHP',
        maximumFractionDigits: 2,
    }).format(value);

    const drawChart = (metric) => {
        if (!chartContainer || !window.analyticsDatasets) {
            return;
        }
        const rows = window.analyticsDatasets[metric] ?? [];
        const width = 900;
        const height = 300;
        const padding = { top: 24, right: 18, bottom: 42, left: 74 };
        const values = rows.map((row) => Number(row.value) || 0);
        const maximum = Math.max(...values, 1);
        const plotWidth = width - padding.left - padding.right;
        const plotHeight = height - padding.top - padding.bottom;
        const points = rows.map((row, index) => {
            const x = rows.length === 1
                ? padding.left + plotWidth / 2
                : padding.left + (index / (rows.length - 1)) * plotWidth;
            const y = padding.top + plotHeight - (Number(row.value) / maximum) * plotHeight;
            return { x, y, ...row };
        });

        if (rows.length === 0) {
            chartContainer.innerHTML = '<div class="analytics-empty analytics-chart-empty">No data available for this reporting period.</div>';
            return;
        }

        const grid = Array.from({ length: 5 }, (_, index) => {
            const y = padding.top + (index / 4) * plotHeight;
            const value = maximum - (index / 4) * maximum;
            const label = metric === 'revenue' || metric === 'payments'
                ? formatCurrency(value)
                : String(Math.round(value));
            return `<line x1="${padding.left}" y1="${y}" x2="${width - padding.right}" y2="${y}" class="analytics-chart-grid"/>
                <text x="${padding.left - 12}" y="${y + 4}" text-anchor="end" class="analytics-chart-axis">${label}</text>`;
        }).join('');
        const polyline = points.map((point) => `${point.x},${point.y}`).join(' ');
        const area = points.length
            ? `M ${points[0].x} ${padding.top + plotHeight} L ${points.map((point) => `${point.x} ${point.y}`).join(' L')} L ${points.at(-1).x} ${padding.top + plotHeight} Z`
            : '';
        const dots = points.map((point) => `
            <circle cx="${point.x}" cy="${point.y}" r="4" class="analytics-chart-dot">
                <title>${point.label}: ${metric === 'revenue' || metric === 'payments' ? formatCurrency(point.value) : point.value}</title>
            </circle>`).join('');
        const labelStep = Math.max(1, Math.ceil(rows.length / 8));
        const labels = points.map((point, index) => index % labelStep === 0 || index === points.length - 1
            ? `<text x="${point.x}" y="${height - 12}" text-anchor="middle" class="analytics-chart-axis">${point.label}</text>`
            : '').join('');

        chartContainer.innerHTML = `<svg viewBox="0 0 ${width} ${height}" preserveAspectRatio="xMidYMid meet" aria-hidden="true">
            ${grid}
            <path d="${area}" class="analytics-chart-area"></path>
            <polyline points="${polyline}" class="analytics-chart-line"></polyline>
            ${dots}${labels}
        </svg>`;
    };

    document.querySelectorAll('[data-chart-metric]').forEach((button) => {
        button.addEventListener('click', () => {
            const metric = button.dataset.chartMetric;
            document.querySelectorAll('[data-chart-metric]').forEach((item) => {
                const active = item === button;
                item.classList.toggle('active', active);
                item.setAttribute('aria-selected', String(active));
            });
            chartTitle.textContent = {
                revenue: 'Net Revenue',
                bookings: 'Booking Count',
                payments: 'Verified Payments',
            }[metric];
            chartHeadline.textContent = metric === 'revenue'
                ? formatCurrency(window.analyticsTotalRevenue)
                : (metric === 'payments' ? formatCurrency(window.analyticsTotalPayments) : String(window.analyticsTotalBookings));
            drawChart(metric);
        });
    });
    drawChart('revenue');

    const reportYear = document.getElementById('report-year');
    const reportQuarter = document.getElementById('report-quarter');
    const reportFrom = document.getElementById('report-from-date');
    const reportTo = document.getElementById('report-to-date');
    const fillQuarterRange = () => {
        if (!reportYear || !reportQuarter || !reportFrom || !reportTo) {
            return;
        }
        const year = Number(reportYear.value);
        const quarter = reportQuarter.value;
        if (quarter === 'Custom') {
            return;
        }
        const startMonth = quarter === 'All' ? 1 : (Number(quarter.slice(1)) - 1) * 3 + 1;
        const endMonth = quarter === 'All' ? 12 : startMonth + 2;
        reportFrom.value = `${year}-${String(startMonth).padStart(2, '0')}-01`;
        const end = new Date(year, endMonth, 0);
        reportTo.value = `${year}-${String(endMonth).padStart(2, '0')}-${String(end.getDate()).padStart(2, '0')}`;
    };
    reportQuarter?.addEventListener('change', fillQuarterRange);
    reportYear?.addEventListener('change', () => {
        if (reportQuarter?.value !== 'Custom') {
            fillQuarterRange();
        }
    });
    [reportFrom, reportTo].forEach((field) => field?.addEventListener('input', () => {
        if (reportQuarter) {
            reportQuarter.value = 'Custom';
        }
    }));
});

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-chat-details-toggle]').forEach((button) => {
        const workspace = button.closest('.chat-workspace');
        const panel = workspace?.querySelector('[data-chat-details-panel]');

        if (!workspace || !panel) {
            return;
        }

        button.addEventListener('click', () => {
            const isOpen = workspace.classList.toggle('chat-workspace--details-open');
            button.setAttribute('aria-expanded', String(isOpen));
            button.setAttribute('aria-label', isOpen ? 'Hide guest information' : 'Show guest information');
            button.title = isOpen ? 'Hide guest information' : 'Show guest information';
            panel.setAttribute('aria-hidden', String(!isOpen));
        });
    });

    document.querySelectorAll('[data-chat-search-form]').forEach((form) => {
        const input = form.querySelector('[data-chat-search-input]');
        const clearButton = form.querySelector('[data-chat-search-clear]');
        const sidebar = form.closest('.chat-sidebar');
        const list = sidebar?.querySelector('[data-chat-inbox-list]');
        const emptyState = sidebar?.querySelector('[data-chat-search-empty]');

        if (!input || !list) {
            return;
        }

        const items = [...list.querySelectorAll('[data-chat-inbox-item]')];
        const applySearch = () => {
            const searchTerm = input.value.trim().toLocaleLowerCase();
            let visibleCount = 0;

            items.forEach((item) => {
                const matches = (item.dataset.chatGuestName ?? '').includes(searchTerm);
                item.hidden = !matches;
                if (matches) {
                    visibleCount += 1;
                }
            });

            if (clearButton) {
                clearButton.hidden = searchTerm.length === 0;
            }
            if (emptyState) {
                emptyState.hidden = searchTerm.length === 0 || visibleCount > 0;
            }
        };

        form.addEventListener('submit', (event) => event.preventDefault());
        input.addEventListener('input', applySearch);
        clearButton?.addEventListener('click', () => {
            input.value = '';
            applySearch();
            input.focus();
        });
        applySearch();
    });

    document.querySelectorAll('[data-guest-chat-launcher]').forEach((launcher) => {
        const closeButton = launcher.querySelector('[data-close-guest-chat]');
        const trigger = launcher.querySelector('.guest-chat-trigger');
        const widget = launcher.querySelector('[data-public-chat-widget]');

        closeButton?.addEventListener('click', () => {
            launcher.open = false;
            trigger?.focus();
        });

        if (!widget) {
            return;
        }

        const houseSelect = widget.querySelector('[data-chat-house-select]');
        const unitSelect = widget.querySelector('[data-chat-unit-select]');
        const unitPicker = widget.querySelector('[data-chat-unit-picker]');
        const transcript = widget.querySelector('[data-chat-transcript]');
        const faqList = widget.querySelector('[data-chat-faqs]');
        const questionForm = widget.querySelector('[data-chat-question-form]');
        const questionInput = widget.querySelector('[data-chat-question-input]');
        const contactForm = widget.querySelector('[data-chat-contact-form]');
        const contactPrompt = widget.querySelector('[data-chat-contact-prompt]');
        const contactMessage = widget.querySelector('[data-chat-contact-message]');
        const contactHouse = widget.querySelector('[data-chat-contact-house]');
        const contactUnit = widget.querySelector('[data-chat-contact-unit]');
        const contactSubject = widget.querySelector('[data-chat-contact-subject]');
        const fixedHouseId = widget.dataset.fixedHouseId;
        const fixedUnitId = widget.dataset.fixedUnitId;
        let contextController;
        let contextKey = '';
        let isSubmitting = false;

        const selectedHouseId = () => fixedHouseId || houseSelect?.value || '';
        const selectedUnitId = () => fixedUnitId || unitSelect?.value || '';
        const scrollTranscript = () => {
            if (transcript) {
                transcript.scrollTop = transcript.scrollHeight;
            }
        };
        const appendChatMessage = (text, sender, link = null) => {
            const message = document.createElement('article');
            message.className = `guest-chat-message guest-chat-message--${sender}`;
            const label = document.createElement('span');
            label.textContent = sender === 'guest' ? 'You' : 'MCTBS Connect';
            const content = document.createElement('p');
            content.textContent = text;
            message.append(label, content);

            if (link) {
                const anchor = document.createElement('a');
                anchor.href = link.url;
                anchor.textContent = link.label;
                if (link.external) {
                    anchor.target = '_blank';
                    anchor.rel = 'noopener noreferrer';
                }
                message.append(anchor);
            }

            transcript?.append(message);
            scrollTranscript();
            return message;
        };
        const populateQuestions = (questions) => {
            if (!faqList) {
                return;
            }

            const heading = document.createElement('span');
            heading.className = 'guest-chat-section-label';
            heading.textContent = 'Quick questions';
            faqList.replaceChildren(heading);
            [...new Set(questions)].forEach((question) => {
                const button = document.createElement('button');
                button.className = 'guest-chat-question';
                button.type = 'button';
                button.dataset.chatQuestion = question;

                const questionIcon = document.createElement('i');
                questionIcon.className = 'fa fa-circle-question';
                questionIcon.setAttribute('aria-hidden', 'true');
                const label = document.createElement('span');
                label.textContent = question;
                const arrowIcon = document.createElement('i');
                arrowIcon.className = 'fa fa-arrow-right';
                arrowIcon.setAttribute('aria-hidden', 'true');
                button.append(questionIcon, label, arrowIcon);
                faqList.append(button);
            });
            faqList.hidden = questions.length === 0;
        };
        const setContactDetails = (question) => {
            if (!contactForm || !contactMessage || !contactPrompt) {
                return;
            }

            contactMessage.value = question;
            contactHouse.value = selectedHouseId();
            contactUnit.value = selectedUnitId();
            contactSubject.value = `Question about ${unitSelect?.selectedOptions?.[0]?.textContent || 'this property'}`;
            contactPrompt.textContent = 'I could not find an approved answer. Share your name and email so the property team can follow up with you here.';
            contactForm.hidden = false;
            contactForm.querySelector('[name="name"]')?.focus();
            scrollTranscript();
        };
        const updateContext = async () => {
            const houseId = selectedHouseId();
            if (!houseId) {
                contextKey = '';
                if (faqList) {
                    faqList.hidden = true;
                }
                if (unitPicker) {
                    unitPicker.hidden = true;
                }
                questionForm?.querySelector('button[type="submit"]')?.setAttribute('disabled', '');
                return;
            }

            const unitId = selectedUnitId();
            const nextContextKey = `${houseId}:${unitId}`;
            if (contextKey === nextContextKey) {
                return;
            }
            contextController?.abort();
            const requestController = new AbortController();
            contextController = requestController;
            contextKey = nextContextKey;

            const contextUrl = new URL(widget.dataset.contextUrl, window.location.origin);
            contextUrl.searchParams.set('house_id', houseId);
            if (unitId) {
                contextUrl.searchParams.set('unit_id', unitId);
            }

            try {
                const response = await fetch(contextUrl, {
                    headers: { Accept: 'application/json' },
                    credentials: 'same-origin',
                    signal: requestController.signal,
                });
                if (!response.ok) {
                    throw new Error(`Chat context request failed with status ${response.status}`);
                }
                const context = await response.json();
                if (contextController !== requestController) {
                    return;
                }

                if (!fixedUnitId && unitSelect) {
                    const selectedId = unitId;
                    unitSelect.replaceChildren(new Option('General property question', ''));
                    context.units.forEach((unit) => unitSelect.add(new Option(unit.name, unit.id)));
                    unitSelect.value = selectedId;
                    if (unitPicker) {
                        unitPicker.hidden = context.units.length === 0;
                    }
                }

                if (faqList) {
                    populateQuestions([...context.quickQuestions, ...context.faqQuestions]);
                }
                questionForm?.querySelector('button[type="submit"]')?.removeAttribute('disabled');
                contactHouse.value = houseId;
                contactUnit.value = unitId;
            } catch (chatError) {
                if (chatError.name === 'AbortError') {
                    return;
                }
                contextKey = '';
                console.error(chatError);
                appendChatMessage('Quick questions could not be loaded. Please try again in a moment.', 'assistant');
            }
        };
        const askQuestion = async (question) => {
            const houseId = selectedHouseId();
            const unitId = selectedUnitId();
            if (!houseId || isSubmitting) {
                return;
            }

            isSubmitting = true;
            questionForm?.querySelector('button[type="submit"]')?.setAttribute('disabled', '');
            appendChatMessage(question, 'guest');
            if (questionInput) {
                questionInput.value = '';
            }

            const formData = new FormData(questionForm);
            formData.set('house_id', houseId);
            formData.set('unit_id', unitId);
            formData.set('question', question);

            try {
                const response = await fetch(widget.dataset.answerUrl, {
                    method: 'POST',
                    headers: { Accept: 'application/json' },
                    credentials: 'same-origin',
                    body: formData,
                });
                if (!response.ok) {
                    throw new Error(`Chat answer request failed with status ${response.status}`);
                }

                const result = await response.json();
                if (result.requiresSignIn) {
                    appendChatMessage(
                        'That request needs a signed-in guest account. Sign in to continue securely.',
                        'assistant',
                        { url: result.signInUrl, label: 'Sign in as a guest' }
                    );
                } else if (result.answered) {
                    appendChatMessage(result.answer, 'assistant');
                    if (result.relatedUrl) {
                        try {
                            const relatedUrl = new URL(result.relatedUrl, window.location.origin);
                            if (['http:', 'https:'].includes(relatedUrl.protocol)) {
                                appendChatMessage('More information is available at the approved link.', 'assistant', {
                                    url: relatedUrl.href,
                                    label: 'More information',
                                    external: relatedUrl.origin !== window.location.origin,
                                });
                            }
                        } catch (urlError) {
                            console.error(urlError);
                        }
                    }
                } else {
                    appendChatMessage('I could not find an approved answer for that question.', 'assistant');
                    setContactDetails(question);
                }
            } catch (chatError) {
                console.error(chatError);
                appendChatMessage('I could not load an answer right now. Please try again or send your question to the property team.', 'assistant');
            } finally {
                isSubmitting = false;
                if (selectedHouseId()) {
                    questionForm?.querySelector('button[type="submit"]')?.removeAttribute('disabled');
                }
            }
        };

        houseSelect?.addEventListener('change', () => {
            if (unitSelect) {
                unitSelect.value = '';
            }
            contextKey = '';
            updateContext();
        });
        unitSelect?.addEventListener('change', () => {
            contextKey = '';
            updateContext();
        });
        questionForm?.addEventListener('submit', (event) => {
            event.preventDefault();
            const question = questionInput?.value.trim();
            if (question) {
                askQuestion(question);
            }
        });
        faqList?.addEventListener('click', (event) => {
            const button = event.target.closest('[data-chat-question]');
            if (button) {
                askQuestion(button.dataset.chatQuestion);
            }
        });
        launcher.addEventListener('toggle', () => {
            if (launcher.open) {
                updateContext();
            }
        });
    });
});
