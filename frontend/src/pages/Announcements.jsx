import './Announcements.css';

function Announcements() {
  const announcements = [
    {
      id: 1,
      title: 'Midterm Examination Schedule Released',
      description:
        'The official schedule for the upcoming Fall Semester midterm examinations is now published. Please check your exam halls and timings carefully.',
      date: 'October 14, 2026',
      publisher: 'Admin',
    },
    {
      id: 2,
      title: 'System Maintenance Window',
      description:
        'The CampusConnect portal will undergo scheduled maintenance on Saturday between 02:00 AM and 04:00 AM UTC. Please save your work beforehand.',
      date: 'October 12, 2026',
      publisher: 'Admin',
    },
    {
      id: 3,
      title: 'Course Enrollment Deadline Reminder',
      description:
        'The deadline for submitting course enrollment and add/drop requests for this semester is Friday, October 20. Ensure all requests are submitted on time.',
      date: 'October 10, 2026',
      publisher: 'Admin',
    },
    {
      id: 4,
      title: 'Campus Library Extended Hours',
      description:
        'The central campus library will extend opening hours until 11:00 PM during exam preparation weeks. Study rooms are open on a first-come, first-served basis.',
      date: 'October 08, 2026',
      publisher: 'Admin',
    },
  ];

  return (
    <div className="announcements-page">
      <header className="announcements-header">
        <h1>Campus Announcements</h1>
        <p>Stay updated with the latest news, notices, and updates from the administration.</p>
      </header>

      {announcements.length > 0 ? (
        <div className="announcements-list">
          {announcements.map((announcement) => (
            <article className="announcement-card" key={announcement.id}>
              <div className="announcement-meta">
                <span className="announcement-publisher">
                  Published by {announcement.publisher}
                </span>
                <span className="announcement-date">{announcement.date}</span>
              </div>

              <h2>{announcement.title}</h2>
              <p>{announcement.description}</p>
            </article>
          ))}
        </div>
      ) : (
        <div className="no-announcements">
          <p>No announcements available at this time. Please check back later for campus updates.</p>
        </div>
      )}
    </div>
  );
}

export default Announcements;
