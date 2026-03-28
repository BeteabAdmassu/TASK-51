import { describe, expect, it } from 'vitest'
import { mount } from '@vue/test-utils'
import ChatMessageBubble from '@/components/chat/ChatMessageBubble.vue'

describe('ChatMessageBubble', () => {
  it('renders own and other messages differently', () => {
    const own = mount(ChatMessageBubble, {
      props: {
        currentUserId: 1,
        message: {
          id: 1,
          type: 'user_message',
          sender_id: 1,
          sender: { username: 'driver01' },
          content: 'Own message',
          created_at: '2026-03-25T10:00:00Z',
        },
      },
    })

    const other = mount(ChatMessageBubble, {
      props: {
        currentUserId: 1,
        message: {
          id: 2,
          type: 'user_message',
          sender_id: 3,
          sender: { username: 'rider01' },
          content: 'Other message',
          created_at: '2026-03-25T10:01:00Z',
        },
      },
    })

    expect(own.find('.bubble').classes()).toContain('own')
    expect(other.find('.bubble').classes()).not.toContain('own')
  })

  it('renders system notices in special style', () => {
    const wrapper = mount(ChatMessageBubble, {
      props: {
        currentUserId: 1,
        message: {
          id: 3,
          type: 'system_notice',
          sender_id: null,
          content: 'Group created',
          created_at: '2026-03-25T10:02:00Z',
        },
      },
    })

    expect(wrapper.find('.notice').exists()).toBe(true)
    expect(wrapper.text()).toContain('Group created')
  })
})
