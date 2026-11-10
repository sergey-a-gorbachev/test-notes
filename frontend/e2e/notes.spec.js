import {expect, test} from '@playwright/test';

const uniqueTitle = (prefix) => `${prefix} ${Date.now()}-${Math.random().toString(16).slice(2)}`;

test('пользователь создаёт заметку', async ({page}) => {
  const title = uniqueTitle('Создание');
  await page.goto('/');
  await page.getByRole('button', {name: /Новая заметка|Создать заметку/}).first().click();
  await page.getByLabel(/Название/).fill(title);
  await page.getByLabel(/Текст заметки/).fill('Текст из E2E-теста');
  await page.getByRole('button', {name: 'Создать заметку'}).click();
  await expect(page.getByText(title)).toBeVisible();
  await expect(page.getByText('Заметка создана')).toBeVisible();
});

test('пользователь редактирует заметку', async ({page, request}) => {
  const title = uniqueTitle('Редактирование');
  const response = await request.post('/api/notes', {data: {title, content: 'До изменения'}});
  expect(response.ok()).toBeTruthy();

  await page.goto('/');
  await page.getByRole('button', {name: `Редактировать ${title}`}).click();
  await page.getByLabel(/Текст заметки/).fill('После изменения');
  await page.getByRole('button', {name: 'Сохранить изменения'}).click();
  await expect(page.getByText('После изменения')).toBeVisible();
});

test('конкурентное изменение блокирует удаление устаревшей версии', async ({page, request}) => {
  const title = uniqueTitle('Конфликт удаления');
  const createdResponse = await request.post('/api/notes', {data: {title, content: 'Исходный текст'}});
  const created = await createdResponse.json();

  await page.goto('/');
  await page.getByRole('button', {name: `Удалить ${title}`}).click();
  const updateResponse = await request.put(`/api/notes/${created.id}`, {
    data: {title, content: 'Изменено конкурентным запросом', version: created.version},
  });
  expect(updateResponse.ok()).toBeTruthy();

  await page.getByRole('button', {name: 'Удалить', exact: true}).click();
  await expect(page.getByRole('alert')).toContainText('не была удалена');
  const currentResponse = await request.get(`/api/notes/${created.id}`);
  expect(currentResponse.ok()).toBeTruthy();
});
