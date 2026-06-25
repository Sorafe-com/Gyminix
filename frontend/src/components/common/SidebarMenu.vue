<script setup>
defineProps({
  items:     { type: Array,   default: () => [] },
  collapsed: { type: Boolean, default: false },
})
</script>

<template>
  <v-list density="compact" nav class="py-1">
    <template v-for="item in items" :key="item.id">
      <!-- Parent with children -->
      <v-list-group v-if="item.children?.length" :value="item.slug">
        <template #activator="{ props }">
          <v-list-item v-bind="props" :value="item.slug" rounded="lg">
            <template #prepend>
              <i :class="[item.icon || 'fa fa-circle-o', 'nav-fa-icon']" />
            </template>
            <v-list-item-title class="text-body-2">{{ item.name }}</v-list-item-title>
          </v-list-item>
        </template>
        <SidebarMenu :items="item.children" :collapsed="collapsed" />
      </v-list-group>

      <!-- Leaf item -->
      <v-list-item
        v-else
        :to="item.route || '#'"
        active-class="v-list-item--active"
        rounded="lg"
      >
        <template #prepend>
          <i :class="[item.icon || 'fa fa-circle-o', 'nav-fa-icon']" />
        </template>
        <v-list-item-title v-show="!collapsed" class="text-body-2">
          {{ item.name }}
        </v-list-item-title>
      </v-list-item>
    </template>
  </v-list>
</template>
