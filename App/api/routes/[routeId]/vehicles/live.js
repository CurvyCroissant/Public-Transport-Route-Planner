import supabase from '../../../_supabase.js'

export default async function handler(req, res) {
  try {
    const m = req.url.match(/\/api\/routes\/(.+?)\/vehicles\/live/)
    const routeId = m ? decodeURIComponent(m[1]) : null
    if (!routeId) return res.status(400).json({ error: 'routeId required' })

    const { data: vehicles, error } = await supabase
      .from('vehicles')
      .select('*')
      .eq('route_id', routeId)
      .eq('live', true)

    if (error) return res.status(500).json({ error: error.message })
    return res.status(200).json(vehicles || [])
  } catch (err) {
    return res.status(500).json({ error: String(err) })
  }
}
