// Small client-side enhancement: confirm cart actions and update visible quantity totals.
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('input.qty').forEach(input => {
    input.addEventListener('change', () => {
      if (Number(input.value) < 0) input.value = 0;
    });
  });
});
