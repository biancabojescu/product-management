export function toIsoDate(value) {
    const match = /^(\d{2})\/(\d{2})\/(\d{4})$/.exec(value.trim());
    if (!match || Number(match[3]) < 1000) return null;
    const iso = `${match[3]}-${match[2]}-${match[1]}`;
    const date = new Date(`${iso}T00:00:00Z`);
    return !Number.isNaN(date.getTime()) && date.toISOString().slice(0, 10) === iso ? iso : null;
}

export function displayDate(value) {
    if (!value) return '';
    const match = /^(\d{4})-(\d{2})-(\d{2})(?:$|[ T])/.exec(value);
    return match ? `${match[3]}/${match[2]}/${match[1]}` : value;
}

export function displayDateTime(value) {
    if (!value) return '—';
    const time = /[ T](\d{2}:\d{2})/.exec(value);
    return displayDate(value) + (time ? ` ${time[1]}` : '');
}

export function validateDateInput(input) {
    const valid = input.value.trim() === '' || toIsoDate(input.value) !== null;
    input.setCustomValidity(valid ? '' : 'Enter a valid date in dd/mm/yyyy format.');
    return valid;
}

export function initDateInput(input) {
    input.addEventListener('input', () => validateDateInput(input));
    input.addEventListener('change', () => validateDateInput(input));
}
