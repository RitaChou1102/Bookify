document.addEventListener('DOMContentLoaded', async () => {
    const app = document.getElementById('app');
    app.innerHTML = '<div style="text-align:center; padding:50px; color:#666;">載入書籍中...</div>';

    try {
        const response = await fetch('/api/books');
        const books = await response.json();

        app.innerHTML = `
            <div style="max-width: 1000px; margin: 0 auto; padding: 40px; font-family: 'Helvetica Neue', sans-serif;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 40px; border-bottom: 2px solid #f3f4f6; padding-bottom: 20px;">
                    <h1 style="font-size: 2.5rem; color: #1f2937; margin: 0;">📚 Bookify 書店</h1>
                    <span style="background: #ecfdf5; color: #047857; padding: 8px 16px; border-radius: 20px; font-weight: bold;">營業中</span>
                </div>
                <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 30px;">
                    ${books.map(book => `
                        <div style="background: white; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1); transition: transform 0.2s; border: 1px solid #e5e7eb;">
                            <div style="height: 12px; background: #3b82f6;"></div>
                            <div style="padding: 24px;">
                                <h2 style="font-size: 1.5rem; font-weight: bold; color: #111; margin-bottom: 8px;">${book.name}</h2>
                                <p style="color: #6b7280; font-size: 0.95rem; margin-bottom: 16px;">✍️ 作者：${book.author}</p>
                                <p style="color: #374151; line-height: 1.6; margin-bottom: 20px; font-size: 0.95rem;">${book.description}</p>
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-top: auto;">
                                    <span style="font-size: 1.25rem; font-weight: bold; color: #059669;">$${book.price}</span>
                                    <button style="background: #2563eb; color: white; border: none; padding: 8px 16px; border-radius: 8px; cursor: pointer; font-weight: 500;">購買</button>
                                </div>
                            </div>
                        </div>
                    `).join('')}
                </div>
            </div>
        `;
    } catch (error) {
        app.innerHTML = `<div style="color:red; text-align:center; padding:50px;">無法讀取資料：${error.message}</div>`;
    }
});