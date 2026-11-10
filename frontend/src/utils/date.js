export const formatDateTime = (date) => new Date(date).toLocaleString('ru-RU', {
  day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit',
});
