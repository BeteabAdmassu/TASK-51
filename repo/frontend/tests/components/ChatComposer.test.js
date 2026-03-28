import { describe, expect, it } from 'vitest'
import { mount } from '@vue/test-utils'
import ChatComposer from '@/components/chat/ChatComposer.vue'

describe('ChatComposer', () => {
  it('updates character counter as user types', async () => {
    const wrapper = mount(ChatComposer)

    await wrapper.find('textarea').setValue('hello')

    expect(wrapper.text()).toContain('5 / 2000')
  })
})
