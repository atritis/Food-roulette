<template>
  <div class="image-uploader">
    <label class="image-uploader__label">Bilder</label>
    <div class="image-uploader__previews" v-if="previews.length">
      <div
        v-for="(preview, index) in previews"
        :key="index"
        class="image-uploader__preview"
      >
        <img :src="preview.url" :alt="`Bild ${index + 1}`" />
        <button
          type="button"
          class="image-uploader__remove"
          @click="removeFile(index)"
        >
          &times;
        </button>
      </div>
    </div>
    <label class="image-uploader__drop">
      <input
        type="file"
        accept="image/jpeg,image/png,image/webp,image/gif"
        multiple
        @change="onFileChange"
        class="sr-only"
      />
      <span>Bilder auswählen oder hierher ziehen</span>
    </label>
  </div>
</template>

<script setup>
import { ref, onUnmounted } from 'vue'

const emit = defineEmits(['update'])
const files = ref([])
const previews = ref([])

function onFileChange(event) {
  const newFiles = Array.from(event.target.files)
  for (const file of newFiles) {
    files.value.push(file)
    const url = URL.createObjectURL(file)
    previews.value.push({ url })
  }
  event.target.value = ''
  emit('update', files.value)
}

function removeFile(index) {
  URL.revokeObjectURL(previews.value[index].url)
  files.value.splice(index, 1)
  previews.value.splice(index, 1)
  emit('update', files.value)
}

onUnmounted(() => {
  previews.value.forEach(p => URL.revokeObjectURL(p.url))
})
</script>

<style lang="scss">
.image-uploader {
  &__label {
    display: block;
    margin-bottom: 0.375rem;
    font-weight: 500;
    font-size: 0.875rem;
    color: var(--color-text-secondary);
  }

  &__previews {
    display: flex;
    flex-wrap: wrap;
    gap: 0.5rem;
    margin-bottom: 0.75rem;
  }

  &__preview {
    position: relative;
    width: 80px;
    height: 80px;
    border-radius: var(--radius-sm);
    overflow: hidden;

    img {
      width: 100%;
      height: 100%;
      object-fit: cover;
    }
  }

  &__remove {
    position: absolute;
    top: 2px;
    right: 2px;
    width: 1.25rem;
    height: 1.25rem;
    border: none;
    background: rgba(0, 0, 0, 0.6);
    color: #fff;
    border-radius: 50%;
    font-size: 0.875rem;
    line-height: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    cursor: pointer;
  }

  &__drop {
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 1.5rem;
    border: 2px dashed var(--color-border);
    border-radius: var(--radius-sm);
    color: var(--color-text-muted);
    font-size: 0.875rem;
    cursor: pointer;
    transition: border-color var(--transition-fast);

    &:hover {
      border-color: var(--color-primary);
    }
  }
}
</style>
