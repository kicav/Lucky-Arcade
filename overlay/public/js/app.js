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
    });
})();
