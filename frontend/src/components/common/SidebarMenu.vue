<script setup>
import { ref } from 'vue'
import { RouterLink } from 'vue-router'

const props = defineProps({ items: { type: Array, default: () => [] } })
const open  = ref({})
const toggle = (slug) => { open.value[slug] = !open.value[slug] }
</script>

<template>
  <ul style="list-style:none;margin:0;padding:0">
    <li v-for="item in items" :key="item.id" class="nav-item">
      <!-- Parent with children -->
      <template v-if="item.children?.length">
        <button class="nav-parent-btn" @click="toggle(item.slug)">
          <i :class="item.icon || 'fa fa-circle-o'" style="width:18px;text-align:center"></i>
          <span style="flex:1">{{ item.name }}</span>
          <i :class="open[item.slug] ? 'fa fa-chevron-up' : 'fa fa-chevron-down'" style="font-size:.65rem;color:#6c7a8d"></i>
        </button>
        <div v-show="open[item.slug]" class="nav-children">
          <SidebarMenu :items="item.children" />
        </div>
      </template>

      <!-- Leaf -->
      <RouterLink v-else :to="item.route || '#'">
        <i :class="item.icon || 'fa fa-circle-o'" style="width:18px;text-align:center"></i>
        <span>{{ item.name }}</span>
      </RouterLink>
    </li>
  </ul>
</template>
