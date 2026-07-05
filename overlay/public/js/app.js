(() => {
    const onReady = (callback) => {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', callback, { once: true });
        } else {
            callback();
        }
    };

    onReady(() => {
        document.querySelectorAll('.js-play-form').forEach((form) => {
            form.addEventListener('submit', () => {
                const button = form.querySelector('button[type="submit"]');
                if (!button) return;
                button.disabled = true;
                button.textContent = button.dataset.loadingText || 'Processing…';
            });
        });

        const die = document.querySelector('.js-dice');
        if (die && die.dataset.finalValue && die.dataset.finalValue !== '—') {
            const finalValue = die.dataset.finalValue;
            let ticks = 0;
            const timer = window.setInterval(() => {
                die.textContent = (Math.random() * 100).toFixed(2);
                ticks += 1;
                if (ticks >= 12) {
                    window.clearInterval(timer);
                    die.textContent = finalValue;
                    die.classList.add('settled');
                }
            }, 55);
        }

        const wheel = document.querySelector('.js-wheel');
        if (wheel) {
            const finalValue = Number.parseInt(wheel.dataset.finalValue || '0', 10);
            const degreesPerNumber = 360 / 37;
            const rotation = 1080 + (360 - (finalValue * degreesPerNumber));
            wheel.style.setProperty('--wheel-rotation', `${rotation}deg`);
            requestAnimationFrame(() => wheel.classList.add('spin-complete'));
        }




        const slotMachine = document.querySelector('.js-slots.slots-settled');
        if (slotMachine) {
            requestAnimationFrame(() => slotMachine.classList.add('spin-complete'));
        }

        const coin = document.querySelector('.js-coin.coin-settled');
        if (coin) {
            requestAnimationFrame(() => coin.classList.add('flip-complete'));
        }


        const playingCard = document.querySelector('.js-playing-card.card-settled');
        if (playingCard) {
            requestAnimationFrame(() => playingCard.classList.add('card-reveal-complete'));
        }

        document.querySelectorAll('.js-copy').forEach((button) => {
            button.addEventListener('click', async () => {
                const target = document.getElementById(button.dataset.copyTarget || '');
                if (!target) return;
                const original = button.textContent;
                try {
                    await navigator.clipboard.writeText(target.textContent.trim());
                    button.textContent = 'Copied';
                } catch (_) {
                    const range = document.createRange();
                    range.selectNodeContents(target);
                    window.getSelection()?.removeAllRanges();
                    window.getSelection()?.addRange(range);
                    button.textContent = 'Select and copy';
                }
                window.setTimeout(() => { button.textContent = original; }, 1600);
            });
        });

        const type = document.querySelector('.js-roulette-type');
        const selection = document.querySelector('.js-roulette-selection');
        const hint = document.querySelector('.js-roulette-hint');

        if (type && selection && hint) {
            const options = {
                straight: { value: '17', placeholder: '0–36', hint: 'Enter a number from 0 to 36.' },
                color: { value: 'red', placeholder: 'red or black', hint: 'Enter red or black.' },
                parity: { value: 'odd', placeholder: 'odd or even', hint: 'Enter odd or even. Zero loses.' },
                range: { value: 'low', placeholder: 'low or high', hint: 'Low is 1–18; high is 19–36. Zero loses.' },
                dozen: { value: '1', placeholder: '1, 2 or 3', hint: '1 = 1–12, 2 = 13–24, 3 = 25–36.' },
            };

            const refreshSelection = (replaceValue = false) => {
                const config = options[type.value] || options.straight;
                if (replaceValue) selection.value = config.value;
                selection.placeholder = config.placeholder;
                hint.textContent = config.hint;
            };

            type.addEventListener('change', () => refreshSelection(true));
            refreshSelection(false);
        }


        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
        const livePollMs = Math.max(1500, Number.parseInt(document.body.dataset.livePollMs || '4000', 10));

        const showLiveToast = (title, message, tone = 'normal') => {
            const region = document.getElementById('live-toast-region');
            if (!region) return;
            const toast = document.createElement('article');
            toast.className = `live-toast ${tone}`;
            const heading = document.createElement('strong');
            heading.textContent = title;
            const copy = document.createElement('p');
            copy.textContent = message;
            toast.append(heading, copy);
            region.appendChild(toast);
            requestAnimationFrame(() => toast.classList.add('visible'));
            window.setTimeout(() => {
                toast.classList.remove('visible');
                window.setTimeout(() => toast.remove(), 250);
            }, 5200);
        };

        const updateNotificationBadge = (count) => {
            const badge = document.getElementById('notification-nav-badge');
            if (!badge) return;
            badge.textContent = String(count || 0);
            badge.classList.toggle('is-hidden', !count);
        };

        const formatNumber = (value) => new Intl.NumberFormat().format(Number(value || 0));
        const formatTime = (value) => {
            if (!value) return '—';
            const date = new Date(value);
            return Number.isNaN(date.getTime()) ? String(value) : date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit', second: '2-digit' });
        };

        const supportThread = document.querySelector('[data-live-support-thread]');
        const supportStatus = document.querySelector('[data-ticket-status]');

        const updateTicketStatus = (status) => {
            if (!supportStatus || !status) return;
            supportStatus.textContent = status.charAt(0).toUpperCase() + status.slice(1);
            supportStatus.className = `status-pill status-${status}`;
        };

        const appendSupportMessages = (messages) => {
            if (!supportThread || !Array.isArray(messages)) return;
            messages.forEach((message) => {
                if (supportThread.querySelector(`[data-message-id="${message.id}"]`)) return;
                const article = document.createElement('article');
                article.className = `ticket-message ${message.is_admin ? 'from-admin' : 'from-player'} live-arrival`;
                article.dataset.messageId = String(message.id);
                const header = document.createElement('div');
                header.className = 'section-head';
                const author = document.createElement('strong');
                author.textContent = message.author || (message.is_admin ? 'Support' : 'Player');
                const time = document.createElement('time');
                time.textContent = message.created_label || formatTime(message.created_at);
                const body = document.createElement('p');
                body.textContent = message.body || '';
                header.append(author, time);
                article.append(header, body);
                supportThread.appendChild(article);
                supportThread.dataset.lastMessageId = String(Math.max(Number(supportThread.dataset.lastMessageId || 0), Number(message.id || 0)));
            });
            if (messages.length) {
                supportThread.lastElementChild?.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            }
        };

        let supportPolling = false;
        const refreshSupportThread = async () => {
            if (!supportThread || supportPolling || document.visibilityState === 'hidden') return;
            supportPolling = true;
            try {
                const url = new URL(supportThread.dataset.messagesUrl, window.location.origin);
                url.searchParams.set('after', supportThread.dataset.lastMessageId || '0');
                const response = await fetch(url, { headers: { Accept: 'application/json' }, credentials: 'same-origin' });
                if (!response.ok) return;
                const payload = await response.json();
                appendSupportMessages(payload.messages || []);
                updateTicketStatus(payload.ticket_status);
                if (payload.next_after) supportThread.dataset.lastMessageId = String(payload.next_after);
            } catch (_) {
                // The normal form workflow remains available when polling is temporarily unavailable.
            } finally {
                supportPolling = false;
            }
        };

        if (supportThread) {
            window.setInterval(refreshSupportThread, Math.max(2500, livePollMs));
        }

        document.querySelectorAll('.js-live-reply-form').forEach((form) => {
            form.addEventListener('submit', async (event) => {
                event.preventDefault();
                const button = form.querySelector('button[type="submit"]');
                const status = form.querySelector('[data-live-reply-status]');
                if (button) button.disabled = true;
                if (status) status.textContent = 'Sending…';
                try {
                    const response = await fetch(form.action, {
                        method: 'POST',
                        body: new FormData(form),
                        credentials: 'same-origin',
                        headers: {
                            Accept: 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': csrfToken,
                        },
                    });
                    const payload = await response.json().catch(() => ({}));
                    if (!response.ok) {
                        const firstError = Object.values(payload.errors || {}).flat()[0] || payload.message || 'Unable to send reply.';
                        throw new Error(firstError);
                    }
                    appendSupportMessages(payload.message ? [payload.message] : []);
                    updateTicketStatus(payload.ticket_status);
                    form.querySelector('textarea[name="message"]').value = '';
                    if (status) status.textContent = 'Sent live.';
                    window.setTimeout(() => { if (status) status.textContent = ''; }, 1800);
                } catch (error) {
                    if (status) status.textContent = error.message || 'Unable to send.';
                } finally {
                    if (button) button.disabled = false;
                }
            });
        });

        const leaguePanel = document.querySelector('[data-live-league]');
        let leagueRefreshing = false;
        const refreshLeague = async () => {
            if (!leaguePanel || leagueRefreshing) return;
            leagueRefreshing = true;
            try {
                const response = await fetch(leaguePanel.dataset.leagueUrl, { headers: { Accept: 'application/json' }, credentials: 'same-origin' });
                if (!response.ok) return;
                const payload = await response.json();
                const body = leaguePanel.querySelector('[data-live-league-body]');
                if (!body) return;
                body.replaceChildren();
                const userId = Number(leaguePanel.dataset.userId || 0);
                (payload.standings || []).forEach((row) => {
                    const tr = document.createElement('tr');
                    if (Number(row.user_id) === userId) tr.className = 'highlight-row';
                    const values = [`#${row.rank}`, row.name, formatNumber(row.score), formatNumber(row.plays), formatNumber(row.wins), formatNumber(row.total_stake), formatNumber(row.net)];
                    values.forEach((value, index) => {
                        const td = document.createElement('td');
                        td.textContent = value;
                        if (index === 2) {
                            const strong = document.createElement('strong');
                            strong.textContent = value;
                            td.replaceChildren(strong);
                        }
                        if (index === 6) td.className = Number(row.net) >= 0 ? 'positive' : 'negative';
                        tr.appendChild(td);
                    });
                    body.appendChild(tr);
                });
                if (!(payload.standings || []).length) {
                    const tr = document.createElement('tr');
                    const td = document.createElement('td');
                    td.colSpan = 7;
                    td.textContent = 'No league activity yet. Play a game to enter automatically.';
                    tr.appendChild(td);
                    body.appendChild(tr);
                }
                const updated = leaguePanel.querySelector('[data-league-updated]');
                if (updated) updated.textContent = `Updated ${formatTime(payload.generated_at)}`;
            } catch (_) {
                // Keep server-rendered standings when the live refresh is unavailable.
            } finally {
                leagueRefreshing = false;
            }
        };

        const adminLivePanel = document.querySelector('[data-admin-live-url]');
        let adminLiveRefreshing = false;
        const refreshAdminLive = async () => {
            if (!adminLivePanel || adminLiveRefreshing || document.visibilityState === 'hidden') return;
            adminLiveRefreshing = true;
            try {
                const response = await fetch(adminLivePanel.dataset.adminLiveUrl, { headers: { Accept: 'application/json' }, credentials: 'same-origin' });
                if (!response.ok) return;
                const payload = await response.json();
                const onlineBody = adminLivePanel.querySelector('[data-live-online-body]');
                const eventsBody = adminLivePanel.querySelector('[data-live-events-body]');
                if (onlineBody) {
                    onlineBody.replaceChildren();
                    (payload.online_users || []).forEach((row) => {
                        const tr = document.createElement('tr');
                        const user = document.createElement('td');
                        const strong = document.createElement('strong');
                        strong.textContent = row.name;
                        const br = document.createElement('br');
                        const small = document.createElement('small');
                        small.textContent = row.email;
                        user.append(strong, br, small);
                        [row.role?.replaceAll('_', ' ') || 'player', row.path || '—', formatTime(row.last_seen_at)].forEach((value, index) => {
                            const td = document.createElement('td');
                            if (index === 1) {
                                const code = document.createElement('code'); code.textContent = value; td.appendChild(code);
                            } else td.textContent = value;
                            tr.appendChild(td);
                        });
                        tr.prepend(user);
                        onlineBody.appendChild(tr);
                    });
                }
                if (eventsBody) {
                    eventsBody.replaceChildren();
                    (payload.recent_events || []).forEach((row) => {
                        const tr = document.createElement('tr');
                        [`#${row.id}`, row.audience, row.topic, row.type, formatTime(row.created_at)].forEach((value, index) => {
                            const td = document.createElement('td');
                            if (index === 3) { const code = document.createElement('code'); code.textContent = value; td.appendChild(code); }
                            else td.textContent = value;
                            tr.appendChild(td);
                        });
                        eventsBody.appendChild(tr);
                    });
                }
                document.querySelector('[data-live-online-count]')?.replaceChildren(document.createTextNode(formatNumber((payload.online_users || []).length)));
                document.querySelector('[data-live-open-tickets]')?.replaceChildren(document.createTextNode(formatNumber(payload.open_tickets)));
                document.querySelector('[data-live-pending-tickets]')?.replaceChildren(document.createTextNode(formatNumber(payload.pending_tickets)));
                document.querySelector('[data-live-event-count]')?.replaceChildren(document.createTextNode(formatNumber((payload.recent_events || []).length)));
                const generated = document.querySelector('[data-live-generated]');
                if (generated) generated.textContent = `Updated ${formatTime(payload.generated_at)}`;
            } catch (_) {
                const generated = document.querySelector('[data-live-generated]');
                if (generated) generated.textContent = 'Connection retrying…';
            } finally {
                adminLiveRefreshing = false;
            }
        };

        if (adminLivePanel) window.setInterval(refreshAdminLive, livePollMs);

        const liveFeedUrl = document.body.dataset.liveFeedUrl;
        if (liveFeedUrl) {
            const cursorKey = 'lucky-arcade-live-cursor-v1';
            let cursor = Number.parseInt(window.sessionStorage.getItem(cursorKey) || '0', 10);
            let livePolling = false;

            const handleLiveEvent = (event) => {
                const payload = event.payload || {};
                if (event.type === 'notification.created') {
                    showLiveToast(payload.title || 'New notification', payload.message || 'Your account has a new update.', 'success');
                } else if (event.type === 'support.message.created') {
                    refreshSupportThread();
                    if (!supportThread) showLiveToast('Support update', payload.excerpt || 'A support ticket has a new message.');
                } else if (event.type === 'league.changed') {
                    refreshLeague();
                } else if (event.type === 'announcement.changed') {
                    showLiveToast('Announcement updated', payload.title || 'Reload the page to view the latest announcement.');
                } else if (event.type === 'game.settled' && payload.balance !== undefined) {
                    document.querySelectorAll('[data-live-balance]').forEach((node) => { node.textContent = formatNumber(payload.balance); });
                }
            };

            const pollLiveFeed = async () => {
                if (livePolling || document.visibilityState === 'hidden') return;
                livePolling = true;
                try {
                    const url = new URL(liveFeedUrl, window.location.origin);
                    url.searchParams.set('after', String(cursor));
                    const response = await fetch(url, { headers: { Accept: 'application/json' }, credentials: 'same-origin' });
                    if (!response.ok) return;
                    const payload = await response.json();
                    updateNotificationBadge(payload.unread_notifications);
                    (payload.events || []).forEach(handleLiveEvent);
                    cursor = Number(payload.next_after || cursor);
                    window.sessionStorage.setItem(cursorKey, String(cursor));
                } catch (_) {
                    // Adaptive polling retries automatically and never blocks the page.
                } finally {
                    livePolling = false;
                }
            };

            pollLiveFeed();
            window.setInterval(pollLiveFeed, livePollMs);
            document.addEventListener('visibilitychange', () => {
                if (document.visibilityState === 'visible') pollLiveFeed();
            });
        }

    });
})();
