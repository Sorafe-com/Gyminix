import { ref, watch } from 'vue'

export function useDebounce(source, delay = 500) {
  const debounced = ref(source.value)
  let timer

  watch(source, (val) => {
    clearTimeout(timer)
    timer = setTimeout(() => { debounced.value = val }, delay)
  })

  return debounced
}
