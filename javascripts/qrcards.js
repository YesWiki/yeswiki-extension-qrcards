ywInitEach('.qrcard-remove-from-print', (button) => {
  button.addEventListener('click', (event) => {
    event.preventDefault()
    button.closest('.qrcard-to-print')?.remove()
  })
})

ywInitEach('.flip-container', (card) => {
  card.addEventListener('touchstart', () => card.classList.toggle('hover'), {
    passive: true,
  })
})
