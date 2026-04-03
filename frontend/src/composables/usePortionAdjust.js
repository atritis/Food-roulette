import { computed } from 'vue'

export function usePortionAdjust(ingredients, originalServings, desiredServings) {
  const adjustedIngredients = computed(() => {
    const multiplier = desiredServings.value / (originalServings.value || 1)

    return ingredients.value.map(ing => {
      if (!ing.quantity) {
        return { ...ing, adjustedQuantity: null }
      }

      const raw = parseFloat(ing.quantity) * multiplier
      return {
        ...ing,
        adjustedQuantity: formatQuantity(raw),
      }
    })
  })

  return { adjustedIngredients }
}

function formatQuantity(value) {
  if (value === 0) return '0'

  // Round to nearest 0.25
  const rounded = Math.round(value * 4) / 4

  if (Number.isInteger(rounded)) {
    return rounded.toString()
  }

  return rounded.toFixed(2).replace(/\.?0+$/, '')
}
