import { describe, expect, it } from 'vitest'
import { mount } from '@vue/test-utils'
import AvailableRideCard from '@/components/driver/AvailableRideCard.vue'

describe('AvailableRideCard', () => {
  it('renders all ride fields', () => {
    const wrapper = mount(AvailableRideCard, {
      props: {
        ride: {
          id: 11,
          origin_address: '123 Main St',
          destination_address: '456 Oak Ave',
          rider_count: 3,
          time_window_start: '2026-03-25T16:00:00.000Z',
          time_window_end: '2026-03-25T18:00:00.000Z',
          notes: 'Need trunk space',
          status: 'matching',
          created_at: new Date().toISOString(),
        },
      },
    })

    expect(wrapper.text()).toContain('123 Main St')
    expect(wrapper.text()).toContain('456 Oak Ave')
    expect(wrapper.text()).toContain('x 3')
    expect(wrapper.text()).toContain('Need trunk space')
    expect(wrapper.text()).toContain('Accept')
  })
})
