import { ref } from 'vue'

export const barActive = ref(false)
export const barWidth  = ref(0)

let _timer = null

export function startBar() {
  clearInterval(_timer)
  barActive.value = true
  barWidth.value  = 12
  _timer = setInterval(() => {
    if (barWidth.value < 80) barWidth.value += (80 - barWidth.value) * 0.1
  }, 150)
}

export function finishBar() {
  clearInterval(_timer)
  barWidth.value = 100
  setTimeout(() => {
    barActive.value = false
    barWidth.value  = 0
  }, 350)
}
