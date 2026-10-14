import { useState } from 'react';
import './Courses.css';

function Courses() {
  const [searchTerm, setSearchTerm] = useState('');
  const [selectedLecturer, setSelectedLecturer] = useState('All');

  const courses = [
    {
      id: 1,
      title: 'Introduction to Programming',
      code: 'SE101',
      lecturer: 'Abebe Kebede',
    },
    {
      id: 2,
      title: 'Database Systems',
      code: 'SE202',
      lecturer: 'Sara Ahmed',
    },
    {
      id: 3,
      title: 'Web Development',
      code: 'SE203',
      lecturer: 'Dawit Tadesse',
    },
    {
      id: 4,
      title: 'Data Structures and Algorithms',
      code: 'SE204',
      lecturer: 'Meron Tesfaye',
    },
  ];

  const lecturers = ['All', ...new Set(courses.map((c) => c.lecturer))];

  const filteredCourses = courses.filter((course) => {
    const term = searchTerm.toLowerCase().trim();
    const matchesSearch =
      !term ||
      course.title.toLowerCase().includes(term) ||
      course.code.toLowerCase().includes(term);
    const matchesLecturer =
      selectedLecturer === 'All' || course.lecturer === selectedLecturer;

    return matchesSearch && matchesLecturer;
  });

  const handleReset = () => {
    setSearchTerm('');
    setSelectedLecturer('All');
  };

  return (
    <div className="courses-page">
      <header className="courses-header">
        <h1>Available Courses</h1>
        <p>Explore courses offered at CampusConnect.</p>
      </header>

      <div className="courses-controls">
        <div className="search-input-wrapper">
          <input
            type="text"
            className="search-input"
            placeholder="Search courses by title (e.g. Web Development) or code (e.g. SE101)..."
            value={searchTerm}
            onChange={(e) => setSearchTerm(e.target.value)}
          />
        </div>

        <div className="filter-select-wrapper">
          <select
            className="lecturer-select"
            value={selectedLecturer}
            onChange={(e) => setSelectedLecturer(e.target.value)}
          >
            {lecturers.map((lecturer) => (
              <option key={lecturer} value={lecturer}>
                {lecturer === 'All' ? 'All Lecturers' : lecturer}
              </option>
            ))}
          </select>
        </div>

        {(searchTerm || selectedLecturer !== 'All') && (
          <button
            type="button"
            className="clear-button"
            onClick={handleReset}
          >
            Clear Filters
          </button>
        )}
      </div>

      {filteredCourses.length > 0 ? (
        <div className="courses-grid">
          {filteredCourses.map((course) => (
            <div className="course-card" key={course.id}>
              <h2>{course.title}</h2>
              <p>
                <strong>Course Code:</strong> {course.code}
              </p>
              <p>
                <strong>Lecturer:</strong> {course.lecturer}
              </p>

              <button type="button" className="enroll-button">
                Request Enrollment
              </button>
            </div>
          ))}
        </div>
      ) : (
        <div className="no-courses">
          <p>
            {searchTerm
              ? `No courses found matching "${searchTerm}". Try checking your spelling or clearing filters.`
              : 'No courses match your selected filter.'}
          </p>
          <button
            type="button"
            className="clear-filters-btn"
            onClick={handleReset}
          >
            Clear Filters
          </button>
        </div>
      )}
    </div>
  );
}

export default Courses;
